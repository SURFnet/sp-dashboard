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

use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Factory\ServiceCommandFactory;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class SamlServiceService
{
    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    /**
     * @var ServiceCommandFactory
     */
    private $factory;

    /**
     * @param ServiceRepository $serviceRepository
     * @param ServiceCommandFactory $factory
     */
    public function __construct(
        ServiceRepository $serviceRepository,
        ServiceCommandFactory $factory
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->factory = $factory;
    }

    /**
     * @return string
     */
    public function createServiceId()
    {
        return (string) Uuid::uuid1();
    }

    /**
     * @param $serviceId
     *
     * @return Service|null
     */
    public function getServiceById($serviceId)
    {
        return $this->serviceRepository->findById($serviceId);
    }

    /**
     * @param Service $service
     *
     * @return EditServiceCommand
     */
    public function buildEditServiceCommand(Service $service)
    {
        return $this->factory->build($service);
    }

    /**
     * @param int $supplierId
     *
     * @return ViewObject\ServiceList
     */
    public function getServiceListForSupplier($supplierId)
    {
        $services = [];

        foreach ($this->serviceRepository->findBySupplierId($supplierId) as $service) {
            $services[] = ViewObject\Service::fromEntity($service);
        }

        return new ViewObject\ServiceList($services);
    }
}
