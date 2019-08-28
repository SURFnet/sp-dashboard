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
use Surfnet\ServiceProviderDashboard\Application\Exception\ServiceNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
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
     * @Route("/entities/{serviceId}", name="entity_list")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     *
     * @param int $serviceId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function listAction($serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);

        $entityList = $this->entityService->getEntityListForService($service);
        $productionEntitiesEnabled = $service->isProductionEntitiesEnabled();
        $serviceName = $service->getName();

        // Try to get a published entity from the session, if there is one, we just published an entity and might need
        // to display the oidc confirmation popup.
        /** @var Entity $publishedEntity */
        $publishedEntity = $this->get('session')->get('published.entity.clone');

        return [
            'no_service_selected' => empty($service),
            'service' => $service,
            'production_entities_enabled' => $productionEntitiesEnabled,
            'entity_list' => $entityList,
            'serviceName' => $serviceName,
            'showOidcPopup' => $this->showOidcPopup($publishedEntity),
            'publishedEntity' => $publishedEntity
        ];
    }

    private function showOidcPopup($publishedEntity)
    {
        if (is_null($publishedEntity)) {
            return false;
        }
        $protocol = $publishedEntity->getProtocol();
        $isOidcProtocol = $protocol === Entity::TYPE_OPENID_CONNECT_TNG || $protocol === Entity::TYPE_OPENID_CONNECT;

        return $publishedEntity && $isOidcProtocol && $publishedEntity->getClientSecret();
    }
}
