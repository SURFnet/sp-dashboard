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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

class EntityDto
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $state;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @param string $id
     * @param string $entityId
     * @param string $environment
     * @param string $state
     */
    public function __construct($id, $entityId, $environment, $state)
    {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->environment = $environment;
        $this->state = $state;
    }


    public static function fromEntity(Entity $entity)
    {
        return new self($entity->getId(), $entity->getEntityId(), $entity->getEnvironment(), $entity->getStatus());
    }

    public static function fromManageTestResult(ManageEntity $manageResponse)
    {
        return new self(
            $manageResponse->getId(),
            $manageResponse->getMetaData()->getEntityId(),
            'test',
            'published'
        );
    }

    public static function fromManageProductionResult(ManageEntity $manageResponse)
    {
        return new self(
            $manageResponse->getId(),
            $manageResponse->getMetaData()->getEntityId(),
            'production',
            'published'
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

    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function getContact()
    {
        return $this->contact;
    }
}
