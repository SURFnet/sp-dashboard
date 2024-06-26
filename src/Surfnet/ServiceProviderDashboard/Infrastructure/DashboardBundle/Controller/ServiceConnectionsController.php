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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class ServiceConnectionsController extends AbstractController
{
    public function __construct(
        private readonly ServiceConnectionService $serviceConnectionService,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[IsGranted("ROLE_SURFCONEXT_REPRESENTATIVE")]
    #[Route(
        path: '/connections',
        name: 'service_connections',
        methods: ['GET']
    )]
    public function __invoke(): RedirectResponse|Response
    {
        $entities = $this->serviceConnectionService->findByInstitutionId($this->getInstitutionId());
        return $this->render(
            '@Dashboard/Service/my-services.html.twig',
            [
                'testIdps' => $entities->getTestIdps(),
                'entities' => $entities,
            ]
        );
    }

    #[IsGranted("ROLE_SURFCONEXT_REPRESENTATIVE")]
    #[Route(
        path: '/connections-download',
        name: 'service_connections_download',
        methods: ['GET']
    )]
    public function download(): Response
    {
        $entities = $this->serviceConnectionService->getExportData($this->getInstitutionId());
        $csv = $this->serializer->serialize($entities, 'csv');
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="surfconext-service-connections.csv"');
        return $response;
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
