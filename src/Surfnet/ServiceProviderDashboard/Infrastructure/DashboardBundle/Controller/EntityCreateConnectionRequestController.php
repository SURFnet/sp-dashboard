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

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CreateConnectionRequestCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\ConnectionRequestContainerType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\ConnectionRequestContainerFromOverviewType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityCreateConnectionRequestController extends AbstractController
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
     * @Route(
     *     "/entity/create-connection-request/{environment}/{manageId}/{serviceId}",
     *     name="entity_published_create_connection_request",
     *     methods={"GET", "POST"})
     *     )
     * @throws Exception
     */
    public function connectionRequestFromEntityAction(
        Request $request,
        int $serviceId,
        string $manageId,
        string $environment
    ): Response {
        $this->validateServiceIsAllowed($serviceId, $manageId, $environment);

        $manageEntity = $this->entityService->getManageEntityById($manageId, $environment);
        $applicant = $this->authorizationService->getContact();
        $command = new CreateConnectionRequestCommand($manageEntity, $applicant);

        $form = $this->createForm(ConnectionRequestContainerType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entity = $this->entityService->getManageEntityById($manageId, $environment);
            $parameters = [
                'entityName' => $entity->getMetaData()->getNameEn(),
                'showOidcPopup' => $this->showOidcPopup($entity),
                'publishedEntity' => $entity
            ];

            if ($this->isCancelAction($form) || !count($command->getConnectionRequests())) {
                return $this->render('@Dashboard/EntityPublished/publishedProduction.html.twig', $parameters);
            }

            if ($form->isValid()) {
                $this->commandBus->handle($command);
                return $this->redirectToRoute('publish_to_production_and_send_connection_request', $parameters);
            }
        }

        return $this->render(
            '@Dashboard/EntityPublished/createConnectionRequest.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route(
     *     "/entity/create-connection-request-from-overview/{environment}/{manageId}/{serviceId}",
     *     name="entity_published_create_connection_request_from_overview",
     *     methods={"GET", "POST"})
     * @throws Exception
     */
    public function connectionRequestFromOverviewAction(
        Request $request,
        int $serviceId,
        string $manageId,
        string $environment
    ): Response {
        $this->validateServiceIsAllowed($serviceId, $manageId, $environment);

        $manageEntity = $this->entityService->getManageEntityById($manageId, $environment);
        $applicant = $this->authorizationService->getContact();
        $command = new CreateConnectionRequestCommand($manageEntity, $applicant);

        $form = $this->createForm(ConnectionRequestContainerFromOverviewType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($this->isCancelAction($form) || !count($command->getConnectionRequests())) {
                return $this->returnToOverview($serviceId);
            }

            if ($form->isValid()) {
                $this->commandBus->handle($command);
                return $this->redirectToRoute('send_connection_request');
            }
        }

        return $this->render(
            '@Dashboard/EntityPublished/createConnectionRequest.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/entity/send-connection-request", name="send_connection_request",methods={"GET"})
     */
    public function sendConnectionRequestAction()
    {
        return $this->render(
            '@Dashboard/EntityPublished/sendConnectionRequest.html.twig'
        );
    }

    /**
     * @Route("/entity/send-connection-request", name="publish_to_production_and_send_connection_request",methods={"GET"})
     */
    public function publishedToProductionAndSendConnectionRequest(array $parameters)
    {
        return $this->render(
            '@Dashboard/EntityPublished/publishedProductionAndConnectionRequest.html.twig',
            $parameters
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

    private function showOidcPopup(?ManageEntity $publishedEntity)
    {
        if (is_null($publishedEntity)) {
            return false;
        }
        $protocol = $publishedEntity->getProtocol()->getProtocol();
        $protocolUsesSecret = $protocol === Constants::TYPE_OPENID_CONNECT_TNG ||
            $protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER ||
            $protocol === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT;

        return $publishedEntity && $protocolUsesSecret && $publishedEntity->getOidcClient()->getClientSecret();
    }
}
