<?php

//declare(strict_types = 1);

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller\Api;

use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Assembler\ServiceStatusAssembler;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly AuthorizationService $authorizationService,
        private readonly ServiceService $serviceService,
        private readonly ServiceStatusService $serviceStatusService,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param  int $id
     * @return JsonResponse
     */
    #[Route(path: '/api/service/status/{id}', name: 'api_service_status', methods: ['GET', 'POST'])]
    public function status($id): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->authorizationService->assertServiceIdAllowed($id);

        $service = $this->serviceService->getServiceById($id);

        $serviceStatusAssembler = new ServiceStatusAssembler(
            $service,
            $this->serviceStatusService,
            $this->translator
        );

        $serviceStatus = $serviceStatusAssembler->getDto();

        return new JsonResponse(
            [
            'service' => $serviceStatus,
            ]
        );
    }
}
