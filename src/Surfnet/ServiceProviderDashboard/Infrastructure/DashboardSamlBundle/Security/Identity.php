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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

/**
 * @method string getUserIdentifier()
 */
class Identity implements \Symfony\Component\Security\Core\User\UserInterface
{
    public function __construct(private Contact $contact)
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
    public function isPartOfTeam($teamName)
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
    public function __toString()
    {
        return $this->contact->getDisplayName();
    }

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    public function getPassword()
    {
        return "admin";
    }

    public function getSalt()
    {
        return "";
    }

    public function eraseCredentials()
    {
        return "";
    }

    public function getUsername()
    {
        return "admin";
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }
}
