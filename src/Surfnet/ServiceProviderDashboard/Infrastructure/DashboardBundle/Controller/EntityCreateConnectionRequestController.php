<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CreateConnectionRequestCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\ConnectionRequestContainerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityCreateConnectionRequestController extends Controller
{
    use EntityControllerTrait;

    private function validateServiceIsAllowed(int $serviceId, string $manageId, string $environment): void
    {
        $entity = $this->entityService->getManageEntityById($manageId, $environment);
        $entityServiceId = $entity->getService()->getId();
        // Verify the Entity Service Id is one of the logged-in users services
        $this->authorizationService->assertServiceIdAllowed($entityServiceId);
        // Don't trust the url provided service id, check it against the Service Id associated with the entity
        if ($entityServiceId !== $serviceId) {
            throw $this->createAccessDeniedException(
                'You are not allowed to view an Entity from another Service'
            );
        }
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/create-connection-request/{environment}/{manageId}/{serviceId}", name="entity_published_create_connection_request")
     * @throws \Exception
     */
    public function connectionRequestFromEntityAction(
        Request $request,
        int $serviceId,
        string $manageId,
        string $environment
    ): Response {
        $this->validateServiceIsAllowed($serviceId, $manageId, $environment);
        $command = new CreateConnectionRequestCommand();
        $form = $this->createForm(ConnectionRequestContainerType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($this->isCancelAction($form)) {
                return $this->returnToOverview($serviceId);
            }

            if ($form->isValid()) {
                $this->commandBus->handle($command);
                $this->addFlash('info', 'entity.create_connection_request.edit.flash.success');
                return $this->returnToOverview($serviceId);
            }
        }

        return $this->render(
            '@Dashboard/EntityPublished/createConnectionRequest.html.twig',
            ['form' => $form->createView()]
        );
    }

    private function returnToOverview(int $serviceId): RedirectResponse
    {
        // Simply return to entity list, no entity was saved
        if ($this->isGranted('ROLE_ADMINISTRATOR')) {
            return $this->redirectToRoute('service_admin_overview', ['serviceId' => $serviceId]);
        }
        return $this->redirectToRoute('service_overview');
    }
}
