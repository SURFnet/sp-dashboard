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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityMergeService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\EntityTypeFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\CreateNewEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\ProtocolChoiceFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityCreateController extends Controller
{
    use EntityControllerTrait;

    /**
     * @var ProtocolChoiceFactory
     */
    private $protocolChoiceFactory;

    public function __construct(
        CommandBus $commandBus,
        EntityService $entityService,
        ServiceService $serviceService,
        AuthorizationService $authorizationService,
        EntityTypeFactory $entityTypeFactory,
        LoadEntityService $loadEntityService,
        ProtocolChoiceFactory $protocolChoiceFactory,
        EntityMergeService $entityMergeService
    ) {
        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->serviceService = $serviceService;
        $this->authorizationService = $authorizationService;
        $this->entityTypeFactory = $entityTypeFactory;
        $this->loadEntityService = $loadEntityService;
        $this->protocolChoiceFactory = $protocolChoiceFactory;
        $this->entityMergeService = $entityMergeService;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route(
     *     "/entity/create/type/{serviceId}/{targetEnvironment}/{inputId}",
     *     defaults={
     *          "targetEnvironment" = "test",
     *     },
     *     name="entity_type"
     * )
     * @Security("has_role('ROLE_USER')")
     * @Template("@Dashboard/EntityType/type.html.twig")
     *
     * @param Request $request
     *
     * @param int $serviceId
     * @param string $targetEnvironment
     * @param string $inputId
     * @return array|RedirectResponse
     */
    public function typeAction(Request $request, $serviceId, string $targetEnvironment, string $inputId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $choices = $this->protocolChoiceFactory->buildOptions();
        $formId = $targetEnvironment . '_' . $service->getGuid();

        $entityList = $this->entityService
            ->getEntityListForService($service)
            ->sortEntitiesByEnvironment()
            ->getEntities();
        $form = $this->createForm(CreateNewEntityType::class, $formId);

        $form->handleRequest($request);

        if ($form->isSubmitted() || $request->isMethod('post')) {
            $protocol = $request->get($formId . '_protocol');
            $environment = $request->get($formId . '_environment');
            $withTemplate = $request->get($formId . '_withtemplate');
            $manageId = false;
            $sourceEnvironment = null;

            if ($withTemplate === 'yes') {
                $template = explode('/', $request->get($formId . '_entityid/value'));
                $manageId = $template[0];
                $sourceEnvironment = $template[1];
            }

            if (!$manageId) {
                return $this->redirectToRoute('entity_add', [
                    'serviceId' => $service->getId(),
                    'targetEnvironment' => $environment,
                    'type' => $protocol
                ]);
            }

            return $this->redirectToRoute('entity_copy', [
                'serviceId' => $service->getId(),
                'manageId' => $manageId,
                'targetEnvironment' => $environment,
                'sourceEnvironment' => $sourceEnvironment,
            ]);
        }

        return [
            'form' => $form->createView(),
            'serviceId' => $service->getId(),
            'environment' => $targetEnvironment,
            'inputId' => $inputId,
            'protocols' => $choices,
            'productionEnabled' => $service->isProductionEntitiesEnabled(),
            'entities' => $entityList,
            'manageId' => $formId,
        ];
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/create/{serviceId}/{type}/{targetEnvironment}", name="entity_add")
     * @Template("@Dashboard/EntityEdit/edit.html.twig")
     * @param Request $request
     * @param int $serviceId
     * @param null|string $targetEnvironment
     * @param null|string $type
     *
     * @return RedirectResponse|Response
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function createAction(Request $request, $serviceId, $targetEnvironment, $type)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        $service = $this->authorizationService->changeActiveService($serviceId);

        if (!$service->isProductionEntitiesEnabled() &&
            $targetEnvironment !== Constants::ENVIRONMENT_TEST
        ) {
            throw $this->createAccessDeniedException(
                'You do not have access to create entities without publishing to the test environment first'
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
                        $response = $this->publishEntity(null, $command, $flashBag);

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
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
        }

        return [
            'form' => $form->createView(),
            'type' => $type,
        ];
    }


    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/copy/{serviceId}/{manageId}/{targetEnvironment}/{sourceEnvironment}",
     *      defaults={
     *          "manageId" = null,
     *          "targetEnvironment" = "test",
     *          "sourceEnvironment" = "test"
     *      },
     *      name="entity_copy"
     * )
     * @Security("has_role('ROLE_USER')")
     * @Template("@Dashboard/EntityEdit/edit.html.twig")
     *
     * @param Request $request
     *
     * @param int $serviceId
     * @param null|string $manageId set from the entity_copy route
     * @param null|string $targetEnvironment set from the entity_copy route
     * @param null|string $sourceEnvironment indicates where the copy command originated from
     *
     * @return RedirectResponse|Response
     *
     * @throws InvalidArgumentException
     * @throws \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function copyAction(Request $request, $serviceId, $manageId, $targetEnvironment, $sourceEnvironment)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        $service = $this->authorizationService->changeActiveService($serviceId);

        $entity = $this->loadEntityService->load(null, $manageId, $service, $sourceEnvironment, $targetEnvironment);
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
                        $response = $this->publishEntity($entity, $command, $flashBag);

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
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
        }

        return [
            'form' => $form->createView(),
            'type' => $entity->getProtocol()->getProtocol(),
        ];
    }
}
