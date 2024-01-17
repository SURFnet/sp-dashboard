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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField Fields of this class are not yet used, remove this once they are used)
 * @method string getUserIdentifier()
 */
#[ORM\Entity(repositoryClass: \Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ContactRepository::class)]
class Contact
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var Collection<Service>
     */
    #[ORM\ManyToMany(targetEntity: 'Service', inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private \Doctrine\Common\Collections\Collection $services;

    private array $roles = [];

    /**
     * @param string $nameId
     * @param string $emailAddress
     * @param string $displayName
     */
    public function __construct(#[ORM\Column(length: 150)]
    private $nameId, #[ORM\Column(length: 255)]
    private $emailAddress, #[ORM\Column(length: 255)]
    private $displayName)
    {
        $this->services = new ArrayCollection();
    }

    /**
     * @param string $emailAddress
     *
     * @return Contact
     */
    public function setEmailAddress($emailAddress): void
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
    public function setDisplayName($displayName): static
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

    public function getNameId(): string
    {
        return $this->nameId;
    }

    /**
     * @return Contact
     */
    public function addService(Service $service): static
    {
        $this->services->add($service);

        return $this;
    }

    /**
     * @return Contact
     */
    public function removeService(Service $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasService(Service $service)
    {
        return $this->services->contains($service);
    }

    /**
     * @return Collection<Service>
     */
    public function getServices(): \Doctrine\Common\Collections\Collection
    {
        return $this->services;
    }

    public function assignRole(string $role) : void
    {
        $this->roles[] = $role;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
