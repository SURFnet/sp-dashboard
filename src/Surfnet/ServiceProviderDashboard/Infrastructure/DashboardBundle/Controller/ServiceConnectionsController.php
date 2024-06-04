<?php

/**
 * Copyright 2024 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Application\Service\ServiceConnectionService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InstitutionIdNotFoundException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ServiceConnectionsController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
        private readonly ServiceConnectionService $serviceConnectionService,
    ) {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMINISTRATOR") or is_granted("ROLE_SURFCONEXT_RESPONSIBLE")'))]
    #[Route(
        path: '/connections/{serviceId}',
        name: 'service_connections',
        methods: ['GET', 'POST']
    )]
    public function myServices(int $serviceId)
    {
        $institutionId = $this->getUser()->getContact()->getInstitutionId();
        if ($institutionId === null) {
            throw new InstitutionIdNotFoundException(
                'ROLE_SURFCONEXT_RESPONSIBLE is granted, but no institution_id was provided in the assertion'
            );
        }
        $service = $this->authorizationService->changeActiveService($serviceId);
        $testIdps = $this->serviceConnectionService->listTestIdps($institutionId);
        $entities = $this->serviceConnectionService->find($service, $institutionId);
        return $this->render(
            '@Dashboard/Service/my-services.html.twig',
            [
                'testIdps' => $testIdps,
                'entities' => $entities,
                'service' => $service,
            ]
        );
    }

}
