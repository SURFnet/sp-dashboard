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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity(repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ContactRepository")
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField Fields of this class are not yet used, remove this once they are used)
 */
class Contact
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     */
    private $nameId;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $displayName;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $emailAddress;

    /**
     * @var Service[]
     *
     * @ORM\ManyToMany(targetEntity="Service", inversedBy="contacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $services;

    /**
     * @param string $nameId
     * @param string $emailAddress
     * @param string $displayName
     */
    public function __construct($nameId, $emailAddress, $displayName)
    {
        $this->nameId = $nameId;
        $this->emailAddress = $emailAddress;
        $this->displayName = $displayName;
    }

    /**
     * @param string $emailAddress
     *
     * @return Contact
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $displayName
     *
     * @return Contact
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param Service[] $services
     *
     * @return Contact
     */
    public function setServices(array $services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @param Service $service
     *
     * @return Contact
     */
    public function hasService(Service $query)
    {
        foreach ($this->services as $service) {
            if ($service->getId() === $query->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Service $service
     *
     * @return Contact
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }
}
