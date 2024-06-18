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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use RuntimeException;
use Surfnet\ServiceProviderDashboard\Application\Exception\ServiceNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationService
{
    /**
     * @var array<string, \Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig>
     */
    public $manageConfig;
    public function __construct(
        private readonly ServiceService $serviceService,
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
        ApiConfig $manageTestConfig,
        ApiConfig $manageProdConfig,
    ) {
        $this->manageConfig = [
            'test' => $manageTestConfig,
            'prod' => $manageProdConfig,
            'production' => $manageProdConfig,
        ];
    }

    public function isLoggedIn(): bool
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return false;
        }

        return (bool) $token->getUser();
    }

    /**
     * Is the logged-in user an administrator?
     */
    public function isAdministrator(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $roles = $this->tokenStorage->getToken()->getRoleNames();
        return in_array('ROLE_ADMINISTRATOR', $roles);
    }

    public function isSurfConextRepresentative()
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $roles = $this->tokenStorage->getToken()->getRoleNames();
        return in_array('ROLE_SURFCONEXT_REPRESENTATIVE', $roles);
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
        if ($token === null) {
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
     */
    private function setSelectedServiceId($serviceId): static
    {
        $this->requestStack->getSession()->set('selected_service_id', $serviceId);

        return $this;
    }

    /**
     * Get the service selected in the service switcher.
     *
     * @return string
     */
    public function getSelectedServiceId()
    {
        $serviceId = $this->requestStack->getSession()->get('selected_service_id');

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
     */
    public function assertServiceIdAllowed($serviceId): void
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
        return $this->requestStack->getSession()->has('selected_service_id');
    }

    /**
     * Get all service names keyed by ID the user has access to.
     */
    public function getAllowedServiceNamesById(): array
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            throw new RuntimeException(
                'No authentication token found in session'
            );
        }

        $serviceNames = $this->serviceService->getServiceNamesById();
        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMINISTRATOR', $roles)) {
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
            function ($id) use ($contact): bool {
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
     * @param $serviceId
     * @throws ServiceNotFoundException
     */
    public function changeActiveService($serviceId): Service
    {
        $service = $this->serviceService->getServiceById($serviceId);

        if (!$service || !$this->hasAccessToService($serviceId)) {
            throw new ServiceNotFoundException('Unable to find service.');
        }

        $this->setSelectedServiceId($service->getId());

        return $service;
    }

    public function resetService(): void
    {
        $this->setSelectedServiceId(null);
    }

    /**
     * @param string $serviceId
     */
    private function hasAccessToService($serviceId): bool
    {
        $allowedServiceIds = array_keys(
            $this->getAllowedServiceNamesById()
        );

        return in_array($serviceId, $allowedServiceIds);
    }

    public function getContact(): Contact
    {
        return $this->tokenStorage->getToken()->getUser()->getContact();
    }
}
