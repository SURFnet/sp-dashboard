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
     * @param string $supplierId
     *
     * @return AuthorizationService
     */
    public function setAdminSwitcherSupplierId($supplierId)
    {
        $this->session->set('admin_supplier_id', $supplierId);

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminSwitcherSupplierId()
    {
        return $this->session->get('admin_supplier_id');
    }

    public function getActiveSupplierId()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new RuntimeException(
                'No authentication token found in session'
            );
        }

        if ($token->hasRole('ROLE_ADMINISTRATOR')) {
            return $this->getAdminSwitcherSupplierId();
        }

        $contact = $token->getUser()->getContact();
        if (!$contact) {
            throw new RuntimeException(
                'No authenticated user found in session'
            );
        }

        $supplier = $contact->getSupplier();
        if (!$supplier) {
            throw new RuntimeException(
                'No supplier found for authenticated user'
            );
        }

        return $contact->getSupplier()->getId();
    }
}
