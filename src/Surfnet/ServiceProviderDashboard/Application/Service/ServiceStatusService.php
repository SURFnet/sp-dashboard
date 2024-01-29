<?php



/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PrivacyQuestionsRepository;

class ServiceStatusService
{
    public function __construct(
        private readonly PrivacyQuestionsRepository $privacyStatusRepository,
        private readonly EntityService $entityService,
    ) {
    }

    /**
     * Test if the service has filled out privacy questions
     *
     * @return bool
     */
    public function hasPrivacyQuestions(Service $service): bool
    {
        // At some point, the privacy questions were answered (they might be all empty now but there is a record)
        return (bool) $this->privacyStatusRepository->findByService($service);
    }

    /**
     * - Status: "No" when no test entity, and no draft on test is present
     * - Status: "In progress" when there is no entity on test but a draft test entity is present
     * - Status: "Yes" when a test entity is published
     *
     * @return string
     */
    public function getEntityStatusOnTest(Service $service): string
    {
        $entities = $this->entityService->getEntitiesForService($service);

        $inProgressList = [];
        $publishedList = [];

        foreach ($entities as $entity) {
            if ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST) {
                if ($entity->getState() == Constants::STATE_PUBLISHED) {
                    $publishedList[] = $entity;
                }
                if ($entity->getState() == Constants::STATE_DRAFT) {
                    $inProgressList[] = $entity;
                }
            }
        }

        // Was one of the entities published?
        if ($publishedList !== []) {
            return Service::ENTITY_PUBLISHED_YES;
        }

        // Was one of the entities drafted?
        if ($inProgressList !== []) {
            return Service::ENTITY_PUBLISHED_IN_PROGRESS;
        }

        // No published or drafted entities discovered, state "No"
        return Service::ENTITY_PUBLISHED_NO;
    }


    /**
     * - Status: "Not requested" when no production entity, is published or has a publish requested status
     * - Status: "Requested" when there is a least 1 entity on production manage with a publication requested status
     * - Status: "Active" when a production entity is published
     *
     * @return string
     */
    public function getConnectionStatus(Service $service): string
    {
        $entities = $this->entityService->getEntitiesForService($service);

        $inProgressList = [];
        $publishedList = [];

        foreach ($entities as $entity) {
            if ($entity->getEnvironment() === Constants::ENVIRONMENT_PRODUCTION) {
                if ($entity->getState() == Constants::STATE_PUBLISHED) {
                    $publishedList[] = $entity;
                }
                if ($entity->getState() == Constants::STATE_PUBLICATION_REQUESTED) {
                    $inProgressList[] = $entity;
                }
            }
        }

        // Was one of the entities published?
        if ($publishedList !== []) {
            return Service::CONNECTION_STATUS_ACTIVE;
        }

        // Was one of the entities requested?
        if ($inProgressList !== []) {
            return Service::CONNECTION_STATUS_REQUESTED;
        }

        // No published or requested entities discovered
        return Service::CONNECTION_STATUS_NOT_REQUESTED;
    }
}
