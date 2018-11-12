<?php

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Assembler\ServiceStatusAssembler;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var ServiceService
     */
    private $serviceService;

    /**
     * @var ServiceStatusService
     */
    private $serviceStatusService;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var EntityService
     */
    private $entityService;

    /**
     * @param RouterInterface $router
     * @param CommandBus $commandBus
     * @param AuthorizationService $authorizationService
     * @param ServiceService $serviceService
     * @param ServiceStatusService $serviceStatusService
     * @param EntityService $entityService
     */
    public function __construct(
        RouterInterface $router,
        CommandBus $commandBus,
        AuthorizationService $authorizationService,
        ServiceService $serviceService,
        ServiceStatusService $serviceStatusService,
        EntityService $entityService
    ) {
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->authorizationService = $authorizationService;
        $this->serviceService = $serviceService;
        $this->serviceStatusService = $serviceStatusService;
        $this->entityService = $entityService;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/api/service/status", name="api_service_status")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function statusAction()
    {
        $service = $this->serviceService->getServiceById(
            $this->authorizationService->getActiveServiceId()
        );

        $serviceLink = $this->router->generate('service_edit', ['id' => $service->getId()]);
        $entityList = $this->entityService->getEntityListForService($service);

        $serviceStatusAssembler = new ServiceStatusAssembler($service, $serviceLink, $this->serviceStatusService, $entityList);
        $serviceStatus = $serviceStatusAssembler->getDto();

        return new JsonResponse([
            'service' => $serviceStatus,
        ]);
    }
}
