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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\Role;

class SamlToken extends AbstractToken
{
    /**
     * @var \SAML2_Assertion
     */
    public $assertion;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles));
    }

    /**
     * @return bool
     */
    public function hasAccessToEntity(Entity $entity)
    {
        return $this->hasAccessToService($entity->getService());
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

        return $contact->getService()->getId() === $service->getId();
    }

    /**
     * Check if token contains given role.
     *
     * @param string $expected
     */
    public function hasRole($expected)
    {
        foreach ($this->getRoles() as $role) {
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
