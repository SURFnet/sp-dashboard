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

use RuntimeException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationService
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        Session $session,
        TokenStorageInterface $tokenStorage
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $serviceId
     *
     * @return AuthorizationService
     */
    public function setSelectedServiceId($serviceId)
    {
        $this->session->set('selected_service_id', $serviceId);

        return $this;
    }

    /**
     * @return string
     */
    public function getSelectedServiceId()
    {
        return $this->session->get('selected_service_id');
    }

    public function getActiveServiceId()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new RuntimeException(
                'No authentication token found in session'
            );
        }

        if ($token->hasRole('ROLE_ADMINISTRATOR')) {
            return $this->getSelectedServiceId();
        }

        $contact = $token->getUser()->getContact();
        if (!$contact) {
            throw new RuntimeException(
                'No authenticated user found in session'
            );
        }

        $service = $contact->getService();
        if (!$service) {
            throw new RuntimeException(
                'No service found for authenticated user'
            );
        }

        return $contact->getService()->getId();
    }
}
