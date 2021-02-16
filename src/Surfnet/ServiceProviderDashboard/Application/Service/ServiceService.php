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
namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class ServiceService
{
    /**
     * @var ServiceRepository
     */
    private $services;

    /**
     * @param ServiceRepository $services
     */
    public function __construct(ServiceRepository $services)
    {
        $this->services = $services;
    }

    /**
     * Retrieve names of all services.
     *
     * Format: [ '<service id>' => '<service display name>' ]
     * @return array
     */
    public function getServiceNamesById()
    {
        $options = [];

        foreach ($this->services->findAll() as $service) {
            $options[$service->getId()] = $service->getName();
        }

        asort($options);

        return $options;
    }

    /**
     * Retrieve service entities based on an array keyed by service id
     *
     * Format [ '<service name>' => '<service entity>' ]
     *
     * @param array $allowedServices The input should be service names keyed by service id.
     *                               As provided by: AuthorizationService::getAllowedServiceNamesById
     * @return array
     */
    public function getServicesByAllowedServices(array $allowedServices)
    {
        $services = [];
        $serviceIds = array_keys($allowedServices);

        foreach ($serviceIds as $serviceId) {
            $service = $this->getServiceById($serviceId);
            if ($service) {
                $services[$service->getName()] = $service;
            }
        }

        ksort($services);

        return $services;
    }

    /**
     * @param int $id
     *
     * @return Service|null
     */
    public function getServiceById($id)
    {
        return $this->services->findById($id);
    }

    public function getServiceByTeamName(?string $serviceTeamName)
    {
        return $this->services->findByTeamName($serviceTeamName);
    }
}
