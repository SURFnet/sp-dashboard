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

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityEditController extends AbstractController
{
    use EntityControllerTrait;

    /**
     * Subscribe to the PRE_SUBMIT form event to be able to import the metadata
     */
    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SUBMIT => 'onPreSubmit'];
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    #[Route(
        path: '/entity/edit/{environment}/{manageId}/{serviceId}',
        name: 'entity_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, string $environment, string $manageId, int $serviceId): Response
    {
        $flashBag = $request->getSession()->getFlashBag();
        $service = $this->serviceService->getServiceById($serviceId);
        $entity = $this->entityService->getManageEntityById($manageId, $environment);
        $entityServiceId = $entity->getService()->getId();
        // Verify the Entity Service Id is one of the logged in users services
        $this->authorizationService->assertServiceIdAllowed($entityServiceId);
        // Don't trust the url provided service id, check it against the Service Id associated with the entity
        if ($entityServiceId !== $serviceId) {
            throw $this->createAccessDeniedException(
                'You are not allowed to view an Entity from another Service'
            );
        }

        $entity->setService($service);

        // Only clear the flash bag when this request did not come from the 'entity_add' action.
        if (!$this->requestFromCreateAction($request)) {
            $flashBag->clear();
        }

        $form = $this->entityTypeFactory->createEditForm($entity, $service, $environment);
        $command = $form->getData();

        $form->handleRequest($request);

        // Import metadata before loading data into the form
        if ($this->isImportAction($form)) {
            // Import metadata before loading data into the form. Rebuild the form with the imported data
            $form = $this->handleImport($request, $command);
        }

        if ($form->isSubmitted()) {
            try {
                if ($this->isPublishAction($form)) {
                    $isProductionEntityEdit = $entity->isPublished() &&
                        $environment === Constants::ENVIRONMENT_PRODUCTION;
                    // Only trigger form validation on publish
                    if ($form->isValid()) {
                        $response = $this->publishEntity($entity, $command, $isProductionEntityEdit, $request);

                        if ($response instanceof Response) {
                            if ($environment !== Constants::ENVIRONMENT_PRODUCTION) {
                                $this->addFlash('info', 'entity.edit.metadata.flash.success');
                            }
                            return $response;
                        }
                    } else {
                        $this->addFlash('error', 'entity.edit.metadata.validation-failed');
                    }
                } elseif ($this->isCancelAction($form)) {
                    // Simply return to entity list, no entity was saved
                    if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                        return $this->redirectToRoute(
                            'service_admin_overview',
                            ['serviceId' => $entity->getService()->getId()]
                        );
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
                'environment' => $environment,
            ]
        );
    }

    /**
     * When the create action (entity_add) unsuccessfully published an entity. The entity_edit action is loaded and the
     * manage error message (publication failed) message should be shown on the edit form.
     *
     * This method tests if the referer is set in the request headers, if so, it tests if the previous request
     * originated from the entity_add action.
     */
    private function requestFromCreateAction(Request $request): bool
    {
        $requestUri = $request->headers->get('referer', false);
        return $requestUri && preg_match('/\/entity\/create/', $requestUri);
    }
}
