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

use Surfnet\ServiceProviderDashboard\Application\Exception\RuntimeException;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceConnectionService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityConnectionCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InstitutionIdNotFoundException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ServiceConnectionsController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationService     $authorizationService,
        private readonly ServiceConnectionService $serviceConnectionService,
        private readonly ServiceService $serviceService,
    ) {
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMINISTRATOR") or is_granted("ROLE_SURFCONEXT_REPRESENTATIVE")'))]
    #[Route(
        path: '/connections',
        name: 'service_connections',
        methods: ['GET', 'POST']
    )]
    public function __invoke(): RedirectResponse|Response
    {
        // The admin overview (which span multiple services) is rendered in a separate action
        if ($this->isGranted('ROLE_ADMINISTRATOR')) {
            return $this->redirect($this->generateUrl('service_admin_overview'));
        }
        $entities = $this->serviceConnectionService->findByInstitutionId($this->getInstitutionId());
        return $this->render(
            '@Dashboard/Service/my-services.html.twig',
            [
                'testIdps' => $entities->getTestIdps(),
                'entities' => $entities,
            ]
        );
    }

    #[IsGranted("ROLE_ADMINISTRATOR")]
    #[Route(
        path: '/admin-connections',
        name: 'admin_service_connections',
        methods: ['GET', 'POST']
    )]
    public function adminConnections(): Response
    {
        $allowedServices = $this->authorizationService->getAllowedServiceNamesById();
        $services = $this->serviceService->getServicesByAllowedServices($allowedServices);
        $serviceCount = count($services);
        if ($serviceCount > 1) {
            $entities = $this->serviceConnectionService->findByServices($services);
        }
        if ($serviceCount === 1) {
            $entities = $this->serviceConnectionService->findByInstitutionId(
                $this->getInstitutionId(),
            );
        }

        if (!isset($entities)) {
            $entities = EntityConnectionCollection::empty();
        }

        return $this->render(
            '@Dashboard/Service/my-services.html.twig',
            [
                'testIdps' => $entities->getTestIdps(),
                'entities' => $entities,
            ]
        );
    }

    private function getInstitutionId(): InstitutionId
    {
        $institutionId = $this->getUser()->getContact()->getInstitutionId();
        if ($institutionId === null) {
            throw new InstitutionIdNotFoundException('Institution id not found on Contact');
        }
        return new InstitutionId($institutionId);
    }
}
