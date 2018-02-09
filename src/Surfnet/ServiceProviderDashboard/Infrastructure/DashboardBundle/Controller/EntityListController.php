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
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EntityListController extends Controller
{
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Application\Service\EntityService
     */
    private $entityService;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Application\Service\ServiceService
     */
    private $serviceService;

    /**
     * @param AuthorizationService $authorizationService
     * @param EntityService $entityService
     */
    public function __construct(
        AuthorizationService $authorizationService,
        EntityService $entityService,
        ServiceService $serviceService
    ) {
        $this->authorizationService = $authorizationService;
        $this->entityService = $entityService;
        $this->serviceService = $serviceService;
    }

    /**
     * @Method("GET")
     * @Route("/", name="entity_list")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function listAction()
    {
        $serviceOptions = $this->authorizationService->getAllowedServiceNamesById();

        if (empty($serviceOptions)) {
            return $this->redirectToRoute('service_add');
        }

        $service = null;
        $serviceName = '';
        $entityList = [];
        $productionEntitiesEnabled = false;

        $activeServiceId = $this->authorizationService->getActiveServiceId();
        if ($activeServiceId) {
            $service = $this->serviceService->getServiceById(
                $this->authorizationService->getActiveServiceId()
            );
        }

        if ($service) {
            $entityList = $this->entityService->getEntityListForService($service);
            $productionEntitiesEnabled = $service->isProductionEntitiesEnabled();
            $serviceName = $service->getName();
        }

        return [
            'no_service_selected' => empty($service),
            'production_entities_enabled' => $productionEntitiesEnabled,
            'entity_list' => $entityList,
            'serviceName' => $serviceName,
        ];
    }
}
