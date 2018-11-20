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
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationService
{
    /**
     * @var ServiceService
     */
    private $serviceService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        ServiceService $serviceService,
        Session $session,
        TokenStorageInterface $tokenStorage
    ) {
        $this->serviceService = $serviceService;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        return (bool) $token->getUser();
    }

    /**
     * Is the logged-in user an administrator?
     *
     * @return bool
     */
    public function isAdministrator()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        return $this->tokenStorage->getToken()->hasRole('ROLE_ADMINISTRATOR');
    }

    /**
     * Does the user have access to a single supplier, or select a supplier in the swithcer?
     *
     * @return bool
     */
    public function hasActiveServiceId()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $activeServiceId = $this->getActiveServiceId();

        return !empty($activeServiceId);
    }

    /**
     * Get the service the user is currently working on.
     *
     * The service can be active in two ways:
     *
     * - the user has access to only one service, the switcher is not shown
     *    and the service is always "active"
     *
     * - the user has access to multiple services and the user selected one
     *   service from the switcher
     *
     * @return string
     */
    public function getActiveServiceId()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new RuntimeException(
                'No authentication token found in session'
            );
        }

        if (!$this->hasSelectedServiceId()) {
            $allowedServices = $this->getAllowedServiceNamesById();

            // If there a user has access to only service - always use it.
            if (count($allowedServices) === 1) {
                $this->setSelectedServiceId(
                    array_keys($allowedServices)[0]
                );
            }
        }

        return $this->getSelectedServiceId();
    }

    /**
     * Tests if the currently active service has it's privacy questions enabled or not.
     *
     * @return bool
     */
    public function hasActivatedPrivacyQuestions()
    {
        $activeServiceId = $this->getActiveServiceId();
        $service = $this->serviceService->getServiceById($activeServiceId);

        return $service->isPrivacyQuestionsEnabled();
    }

    /**
     * Explicitly select a service for users that have access to multiple services.
     *
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
     * Get the service selected in the service switcher.
     *
     * @return string
     */
    public function getSelectedServiceId()
    {
        $serviceId = $this->session->get('selected_service_id');

        if ($serviceId && !$this->hasAccessToService($serviceId)) {
            throw new RuntimeException(
                sprintf('User is not granted access to service with ID %d', $serviceId)
            );
        }

        return $serviceId;
    }

    /**
     * Get the service selected in the service switcher.
     *
     * @param int $serviceId
     * @return string
     */
    public function assertServiceIdAllowed($serviceId)
    {
        if ($serviceId && !$this->hasAccessToService($serviceId)) {
            throw new RuntimeException(
                sprintf('User is not granted access to service with ID %d', $serviceId)
            );
        }
    }

    /**
     * Did the user select a service in the switcher?
     *
     * @return bool
     */
    public function hasSelectedServiceId()
    {
        return $this->session->has('selected_service_id');
    }


    /**
     * Get all service names keyed by ID the user has access to.
     *
     * @return array
     */
    public function getAllowedServiceNamesById()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            throw new RuntimeException(
                'No authentication token found in session'
            );
        }

        $serviceNames = $this->serviceService->getServiceNamesById();

        if ($token->hasRole('ROLE_ADMINISTRATOR')) {
            return $serviceNames;
        }

        $contact = $token->getUser()->getContact();
        if (!$contact) {
            throw new RuntimeException(
                'No authenticated user found in session'
            );
        }

        return array_filter(
            $serviceNames,
            function ($id) use ($contact) {
                foreach ($contact->getServices() as $service) {
                    if ($service->getId() === $id) {
                        return true;
                    }
                }

                return false;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param string $serviceId
     */
    private function hasAccessToService($serviceId)
    {
        $allowedServiceIds = array_keys(
            $this->getAllowedServiceNamesById()
        );

        return in_array($serviceId, $allowedServiceIds);
    }
}
