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
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param CommandBus $commandBus
     * @param AuthorizationService $authorizationService
     * @param ServiceService $serviceService
     * @param ServiceStatusService $serviceStatusService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        CommandBus $commandBus,
        AuthorizationService $authorizationService,
        ServiceService $serviceService,
        ServiceStatusService $serviceStatusService,
        TranslatorInterface $translator
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationService = $authorizationService;
        $this->serviceService = $serviceService;
        $this->serviceStatusService = $serviceStatusService;
        $this->translator = $translator;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/api/service/status/{id}", name="api_service_status")
     * @Security("has_role('ROLE_USER')")
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function statusAction($id)
    {
        $this->authorizationService->assertServiceIdAllowed($id);

        $service = $this->serviceService->getServiceById($id);

        $serviceStatusAssembler = new ServiceStatusAssembler(
            $service,
            $this->serviceStatusService,
            $this->translator
        );

        $serviceStatus = $serviceStatusAssembler->getDto();

        return new JsonResponse([
            'service' => $serviceStatus,
        ]);
    }
}
