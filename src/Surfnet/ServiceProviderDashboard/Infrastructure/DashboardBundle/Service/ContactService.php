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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ContactService implements UserProviderInterface
{
    /**
     * @param string $nameId
     *
     * @return Contact|null
     */
    public function findByNameId($nameId)
    {
        return new Contact($nameId, '', '');
    }

    public function createContact(Contact $contact)
    {
    }

    public function updateContact(Contact $contact)
    {
    }

    public function loadUserByUsername($username)
    {
        throw new RuntimeException(sprintf('Cannot Load User By Username "%s"', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        throw new RuntimeException(sprintf('Cannot Refresh User "%s"', $user->getUsername()));
    }

    public function supportsClass($class)
    {
        return $class === Contact::class;
    }
}
