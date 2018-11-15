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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CopyEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Exception\ServiceNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
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
     * The create action serves two routes.
     *
     *  1. entity_add to create new entities.
     *
     *  2. entity_copy to copy entities from Manage into the SP Dashboard.
     *     The Entity is copied from the manage test environment to become either a SP Dashboard test-draft or
     *     production-draft entity.
     *
     * @Method({"GET", "POST"})
     * @Route("/entity/create/{targetEnvironment}",
     *      defaults={
     *          "manageId" = null,
     *          "targetEnvironment" = "test",
     *          "sourceEnvironment" = "test"
     *      },
     *      name="entity_add"
     * )
     * @Route("/entity/copy/{manageId}/{targetEnvironment}/{sourceEnvironment}",
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
     * @param null|string $manageId set from the entity_copy route
     * @param null|string $targetEnvironment set from the entity_copy route
     * @param null|string $sourceEnvironment indicates where the copy command originated from
     *
     * @return RedirectResponse|Response
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function createAction(Request $request, $manageId, $targetEnvironment, $sourceEnvironment)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        $service = $this->getService();

        if (!$service->isProductionEntitiesEnabled() &&
            $targetEnvironment !== Entity::ENVIRONMENT_TEST &&
            !$this->isCopyRoute($request)
        ) {
            throw $this->createAccessDeniedException(
                'You do not have access to create entities without publishing to the test environment first'
            );
        }

        $command = SaveEntityCommand::forCreateAction($service);
        $command->setEnvironment($targetEnvironment);

        $form = $this->createForm(EntityType::class, $command, $this->buildOptions($targetEnvironment));

        if ($this->isCopyRoute($request)) {
            if (!$request->isMethod('post')) {
                $form = $this->handleCopy($command, $service, $manageId, $targetEnvironment, $sourceEnvironment);
            }

            // A copy can never be saved as draft: changes are published directly to manage.
            $form->remove('save');
        }

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
        }

        if ($this->isImportAction($form)) {
            // Import metadata before loading data into the form. Rebuild the form with the imported data
            $form = $this->handleImport($request, $command);
        }

        if ($form->isSubmitted()) {
            try {
                if ($this->isSaveAction($form)) {
                    // Only trigger form validation on publish
                    $this->commandBus->handle($command);

                    return $this->redirectToRoute('entity_list');
                } elseif ($this->isPublishAction($form)) {
                    // Only trigger form validation on publish
                    if ($form->isValid()) {
                        $this->commandBus->handle($command);

                        $entity = $this->entityService->getEntityById($command->getId());
                        $response = $this->publishEntity($entity, $flashBag);

                        // When a response is returned, publishing was a success
                        if ($response instanceof Response) {
                            return $response;
                        }

                        // When publishing failed, forward to the edit action and show the error messages there
                        return $this->redirectToRoute('entity_edit', ['id' => $entity->getId()]);
                    }
                    $this->addFlash('error', 'entity.edit.metadata.validation-failed');
                } elseif ($this->isCancelAction($form)) {
                    // Simply return to entity list, no entity was saved
                    return $this->redirectToRoute('entity_list');
                }
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function isCopyRoute(Request $request)
    {
        return 'entity_copy' === $request->get('_route', false);
    }

    /**
     * @return Service
     * @throws ServiceNotFoundException
     */
    private function getService()
    {
        $activeServiceId = $this->authorizationService->getActiveServiceId();
        if ($activeServiceId) {
            return $this->serviceService->getServiceById(
                $this->authorizationService->getActiveServiceId()
            );
        }
        throw new ServiceNotFoundException('Please select a service before adding/copying an entity.');
    }

    /**
     * @param SaveEntityCommand $command
     * @param Service $service
     * @param $manageId
     * @param string $targetEnvironment
     * @param string $sourceEnvironment the env where the copy command originated from
     *
     * @return FormInterface|RedirectResponse
     *
     * @throws InvalidArgumentException
     */
    private function handleCopy(
        SaveEntityCommand $command,
        Service $service,
        $manageId,
        $targetEnvironment,
        $sourceEnvironment
    ) {
        $entityId = $this->entityService->createEntityUuid();

        $this->commandBus->handle(
            new CopyEntityCommand($command, $entityId, $manageId, $service, $targetEnvironment, $sourceEnvironment)
        );

        $form = $this->createForm(EntityType::class, $command, $this->buildOptions($targetEnvironment));
        $form->remove('save');

        return $form;
    }
}
