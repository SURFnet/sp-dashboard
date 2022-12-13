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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class SamlToken extends PostAuthenticationToken
{
    public function __construct(UserInterface $user, string $firewallName, array $roles, array $attributes)
    {
        parent::__construct($user, $firewallName, $roles);

        $this->setAttributes($attributes);
    }

    /**
     * @return bool
     */
    public function hasAccessToEntity(Service $service)
    {
        return $this->hasAccessToService($service);
    }

    /**
     * @return bool
     */
    public function hasAccessToService(Service $service)
    {
        if ($this->hasRole('ROLE_ADMINISTRATOR')) {
            return true;
        }

        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        $contact = $user->getContact();
        if (!$contact) {
            return false;
        }

        foreach ($contact->getServices() as $grantedService) {
            if ($grantedService->getId() === $service->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if token contains given role.
     *
     * @param string $expected
     * @return bool
     */
    public function hasRole($expected)
    {
        foreach ($this->getRoleNames() as $role) {
            if ($role instanceof Role) {
                $role = $role->getRole();
            }

            if ($role === $expected) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }
}
