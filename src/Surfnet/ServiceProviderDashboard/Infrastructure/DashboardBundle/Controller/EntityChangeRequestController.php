<?php

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
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\ChangeRequestService;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityChangeRequestController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/change-request/{environment}/{manageId}/{serviceId}", name="entity_published_change_request")
     */
    public function changeRequestAction(
        Request $request,
        ChangeRequestService $service,
        int $serviceId,
        string $manageId,
        string $environment
    ): Response {
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

        $changeRequests = $service->findById($manageId);

        return $this->render(
            '@Dashboard/EntityPublished/changeRequest.html.twig',
            ['changeRequests' => $changeRequests]
        );
    }
}