<?php

declare(strict_types = 1);

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
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ContactRepository;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField Fields of this class are not yet used, remove this once they are used)
 * @method                                    string getUserIdentifier()
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @var Collection<Service>
     */
    #[ORM\ManyToMany(targetEntity: 'Service', inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $services;

    private array $roles = [];

    public function __construct(
        #[ORM\Column(length: 150)] private readonly string $nameId,
        #[ORM\Column(length: 255)] private string          $emailAddress,
        #[ORM\Column(length: 255)] private string          $displayName,
    ) {
        $this->services = new ArrayCollection();
    }

    public function setEmailAddress(string $emailAddress): Contact
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getNameId(): string
    {
        return $this->nameId;
    }

    public function addService(Service $service): static
    {
        $this->services->add($service);

        return $this;
    }

    public function removeService(Service $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function hasService(Service $service): bool
    {
        return $this->services->contains($service);
    }

    /**
     * @return Collection<Service>
     */
    public function getServices(): Collection
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
