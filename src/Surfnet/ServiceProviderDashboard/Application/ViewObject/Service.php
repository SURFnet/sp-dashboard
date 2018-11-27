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
namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service as DomainService;
use Symfony\Component\Routing\RouterInterface;

class Service
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var EntityList
     */
    private $entityList;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param string $id
     * @param string $name
     * @param EntityList $entityList
     * @param RouterInterface $router
     */
    public function __construct($id, $name, EntityList $entityList, RouterInterface $router)
    {
        $this->id = $id;
        $this->name = $name;
        $this->entityList = $entityList;
        $this->router = $router;
    }

    public static function fromService(DomainService $service, EntityList $entityList, RouterInterface $router)
    {
        return new self(
            $service->getId(),
            $service->getName(),
            $entityList,
            $router
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return EntityList
     */
    public function getEntityList()
    {
        return $this->entityList;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->router->generate('select_service', ['service' => $this->getId()]);
    }
}
