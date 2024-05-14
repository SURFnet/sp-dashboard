<?php

/**
 * Copyright 2017 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use League\Tactician\CommandBus;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityMergeService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\EntityTypeFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\CreateNewEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\ProtocolChoiceFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityCreateController extends AbstractController
{
    use EntityControllerTrait;

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EntityService $entityService,
        private readonly ServiceService $serviceService,
        private readonly AuthorizationService $authorizationService,
        private readonly EntityTypeFactory $entityTypeFactory,
        private readonly LoadEntityService $loadEntityService,
        private readonly ProtocolChoiceFactory $protocolChoiceFactory,
        private readonly EntityMergeService $entityMergeService,
    ) {
    }

    /**
     * @param  int $serviceId
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/entity/create/type/{serviceId}/{targetEnvironment}/{inputId}',
        name: 'entity_type',
        defaults: ['targetEnvironment' => 'test'],
        methods: ['GET', 'POST']
    )]
    public function type(
        Request $request,
        $serviceId,
        string $targetEnvironment,
        string $inputId,
    ): RedirectResponse|Response {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $choices = $this->protocolChoiceFactory->buildOptions();
        if (!$service->isClientCredentialClientsEnabled()) {
            unset($choices['entity.type.oauth20.ccc.title']);
        }
        $formId = $targetEnvironment . '_' . $service->getGuid();
        $entityList = $this->entityService
            ->getEntityListForService($service)
            ->sortEntitiesByEnvironment();
        $form = $this->createForm(CreateNewEntityType::class, $formId);

        $form->handleRequest($request);

        if ($form->isSubmitted() || $request->isMethod('post')) {
            $protocol = $request->get($formId . '_protocol');
            $environment = $request->get($formId . '_environment');
            $withTemplate = $request->get($formId . '_withtemplate');
            $manageId = false;
            $sourceEnvironment = null;

            if ($withTemplate === 'yes') {
                $template = explode('/', (string) $request->get($formId . '_entityid/value'));
                $manageId = $template[0];
                $sourceEnvironment = $template[1];
            }

            if (!$manageId) {
                return $this->redirectToRoute(
                    'entity_add',
                    [
                    'serviceId' => $service->getId(),
                    'targetEnvironment' => $environment,
                    'type' => $protocol,
                    ]
                );
            }

            return $this->redirectToRoute(
                'entity_copy',
                [
                'serviceId' => $service->getId(),
                'manageId' => $manageId,
                'targetEnvironment' => $environment,
                'sourceEnvironment' => $sourceEnvironment,
                ]
            );
        }

        return $this->render(
            '@Dashboard/EntityType/type.html.twig',
            [
            'form' => $form->createView(),
            'serviceId' => $service->getId(),
            'environment' => $targetEnvironment,
            'inputId' => $inputId,
            'protocols' => $choices,
            'entities' => $entityList->getEntities(),
            'manageId' => $formId,
            ]
        );
    }

    /**
     * @param int         $serviceId
     * @param null|string $targetEnvironment
     * @param null|string $type
     *
     * @return RedirectResponse|Response|array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    #[Route(path: '/entity/create/{serviceId}/{type}/{targetEnvironment}', name: 'entity_add', methods: ['GET', 'POST'])]
    public function create(Request $request, $serviceId, $targetEnvironment, $type): Response
    {
        $request->getSession()->getFlashBag()->clear();

        $service = $this->authorizationService->changeActiveService($serviceId);
        $hasTestEntities = $this->entityService
            ->getEntityListForService($service)->hasTestEntities();

        if (!$hasTestEntities && $targetEnvironment !== Constants::ENVIRONMENT_TEST) {
            throw $this->createAccessDeniedException(
                'You do not have access to create entities without publishing to the test environment first'
            );
        }

        if (!$service->isClientCredentialClientsEnabled() && $type === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT) {
            throw $this->createAccessDeniedException(
                'You cannot create client credential clients entities because they are not allowed for this service'
            );
        }

        $form = $this->entityTypeFactory->createCreateForm($type, $service, $targetEnvironment);
        $command = $form->getData();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
        }

        if ($this->isImportAction($form)) {
            // Import metadata before loading data into the form. Rebuild the form with the imported data
            $form = $this->handleImport($request, $command);
        }

        if ($form->isSubmitted()) {
            try {
                if ($this->isPublishAction($form)) {
                    // Only trigger form validation on publish
                    if ($form->isValid()) {
                        $response = $this->publishEntity(null, $command, false, $request);

                        // When a response is returned, publishing was a success
                        if ($response instanceof Response) {
                            return $response;
                        }
                    } else {
                        $this->addFlash('error', 'entity.edit.metadata.validation-failed');
                    }
                } elseif ($this->isCancelAction($form)) {
                    // Simply return to entity list, no entity was saved
                    if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                        return $this->redirectToRoute('service_admin_overview', ['serviceId' => $service->getId()]);
                    }

                    return $this->redirectToRoute('service_overview');
                }
            } catch (InvalidArgumentException) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
        }

        return $this->render(
            '@Dashboard/EntityEdit/edit.html.twig',
            [
            'form' => $form->createView(),
            'type' => $type,
            ]
        );
    }

    /**
     *
     *
     * @param int         $serviceId
     * @param null|string $manageId          set from the entity_copy route
     * @param null|string $targetEnvironment set from the entity_copy route
     * @param null|string $sourceEnvironment indicates where the copy command originated from
     *
     * @return RedirectResponse|Response|array
     *
     * @throws                                       InvalidArgumentException
     * @throws                                       \Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/entity/copy/{serviceId}/{manageId}/{targetEnvironment}/{sourceEnvironment}',
        name: 'entity_copy',
        defaults: [
            'manageId' => null,
            'targetEnvironment' => 'test',
            'sourceEnvironment' => 'test',
        ],
        methods: ['GET', 'POST']
    )]
    public function copy(
        Request $request,
        $serviceId,
        string $manageId,
        string $targetEnvironment,
        string $sourceEnvironment,
    ): Response {
        $flashBag = $request->getSession()->getFlashBag();
        $flashBag->clear();

        $service = $this->authorizationService->changeActiveService($serviceId);

        $entity = $this->loadEntityService->load(
            $manageId,
            $service,
            $sourceEnvironment,
            $targetEnvironment
        );
        $entity->getAllowedIdentityProviders()->clear();
        $entity->setEnvironment($targetEnvironment);

        // load entity into form
        $form = $this->entityTypeFactory->createEditForm($entity, $service, $targetEnvironment, true);
        $command = $form->getData();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
        }

        if ($this->isImportAction($form)) {
            // Import metadata before loading data into the form. Rebuild the form with the imported data
            $form = $this->handleImport($request, $command);
        }

        if ($form->isSubmitted()) {
            try {
                if ($this->isPublishAction($form)) {
                    // Only trigger form validation on publish
                    if ($form->isValid()) {
                        $entity = $entity->resetId();
                        $response = $this->publishEntity($entity, $command, false, $request);

                        // When a response is returned, publishing was a success
                        if ($response instanceof Response) {
                            return $response;
                        }

                        // When publishing failed, forward to the edit action and show the error messages there
                        if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                            return $this->redirectToRoute('service_admin_overview', ['serviceId' => $service->getId()]);
                        }

                        return $this->redirectToRoute('service_overview');
                    } else {
                        $this->addFlash('error', 'entity.edit.metadata.validation-failed');
                    }
                } elseif ($this->isCancelAction($form)) {
                    // Simply return to entity list, no entity was saved
                    if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                        return $this->redirectToRoute('service_admin_overview', ['serviceId' => $service->getId()]);
                    }

                    return $this->redirectToRoute('service_overview');
                }
            } catch (InvalidArgumentException) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
        }

        return $this->render(
            '@Dashboard/EntityEdit/edit.html.twig',
            [
            'form' => $form->createView(),
            'type' => $entity->getProtocol()->getProtocol(),
            ]
        );
    }
}
