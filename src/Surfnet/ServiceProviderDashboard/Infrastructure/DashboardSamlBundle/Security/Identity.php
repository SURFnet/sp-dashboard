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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security;

use BadMethodCallException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Security\Core\User\UserInterface;

class Identity implements UserInterface, \Stringable
{
    public function __construct(private readonly Contact $contact)
    {
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param string $teamName
     * @return bool
     */
    public function isPartOfTeam($teamName): bool
    {
        $services = $this->getContact()->getServices();
        /** @var Service $service */
        foreach ($services as $service) {
            if ($service->getTeamName() === $teamName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->contact->getDisplayName();
    }

    public function getRoles()
    {
        return $this->getContact()->getRoles();
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function eraseCredentials()
    {
    }

    public function getUsername()
    {
        return $this->contact->getNameId();
    }

    public function getUserIdentifier()
    {
        return $this->getUsername();
    }
}
