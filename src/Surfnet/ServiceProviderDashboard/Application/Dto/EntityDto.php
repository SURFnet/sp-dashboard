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

namespace Surfnet\ServiceProviderDashboard\Application\Dto;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class EntityDto
{
    private ?\Surfnet\ServiceProviderDashboard\Domain\Entity\Contact $contact = null;

    /**
     * @param string $id
     * @param string $entityId
     * @param string $environment
     * @param string $state
     * @param string $protocol
     */
    private function __construct(private $id, private $entityId, private $environment, private $state, private $protocol)
    {
    }

    public static function fromManageTestResult(ManageEntity $manageResponse): self
    {
        return new self(
            $manageResponse->getId(),
            $manageResponse->getMetaData()->getEntityId(),
            Constants::ENVIRONMENT_TEST,
            Constants::STATE_PUBLISHED,
            $manageResponse->getProtocol()->getProtocol()
        );
    }

    public static function fromManageProductionResult(ManageEntity $manageResponse): self
    {
        $state = Constants::STATE_PUBLISHED;
        if ($manageResponse->getMetaData()->getCoin()->getExcludeFromPush()) {
            $state = Constants::STATE_PUBLICATION_REQUESTED;
        }
        return new self(
            $manageResponse->getId(),
            $manageResponse->getMetaData()->getEntityId(),
            Constants::ENVIRONMENT_PRODUCTION,
            $state,
            $manageResponse->getProtocol()->getProtocol()
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
    }

    public function getContact(): ?\Surfnet\ServiceProviderDashboard\Domain\Entity\Contact
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}
