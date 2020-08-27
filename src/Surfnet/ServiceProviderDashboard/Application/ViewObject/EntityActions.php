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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class EntityActions
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $serviceId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var bool
     */
    private $readOnly;

    /**
     * @param string $id
     * @param int $serviceId
     * @param string $status
     * @param string $environment
     * @param string $protocol
     * @param bool $isReadOnly
     */
    public function __construct($id, $serviceId, $status, $environment, $protocol, $isReadOnly)
    {
        $this->id = $id;
        $this->serviceId = $serviceId;
        $this->status = $status;
        $this->environment = $environment;
        $this->protocol = $protocol;
        $this->readOnly = $isReadOnly;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function allowEditAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function allowCopyAction()
    {
        if ($this->readOnly) {
            return false;
        }
        $isPublishedTestEntity = ($this->status == DomainEntity::STATE_PUBLISHED
            && $this->environment == DomainEntity::ENVIRONMENT_TEST);

        $isPublishedProdEntity = ($this->status == DomainEntity::STATE_PUBLICATION_REQUESTED
            && $this->environment == DomainEntity::ENVIRONMENT_PRODUCTION);

        return $isPublishedTestEntity || $isPublishedProdEntity;
    }

    public function allowCopyToProductionAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == DomainEntity::STATE_PUBLISHED && $this->environment == DomainEntity::ENVIRONMENT_TEST;
    }

    public function allowCloneAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == DomainEntity::STATE_PUBLISHED && $this->environment == DomainEntity::ENVIRONMENT_PRODUCTION;
    }

    public function allowDeleteAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return !$this->isDeleteRequested();
    }

    /**
     * @return bool
     */
    public function allowAclAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == DomainEntity::STATE_PUBLISHED &&
            $this->environment == DomainEntity::ENVIRONMENT_TEST &&
            $this->protocol !== DomainEntity::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
    }

    /**
     * @return bool
     */
    public function allowSecretResetAction()
    {
        if ($this->readOnly) {
            return false;
        }
        $protocol = $this->protocol;
        $status = $this->status;
        $meetsProtocolRequirement = $protocol == DomainEntity::TYPE_OPENID_CONNECT_TNG ||
            $protocol == DomainEntity::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
        $meetsPublicationStatusRequirement = ($status == DomainEntity::STATE_PUBLISHED || $status == DomainEntity::STATE_PUBLICATION_REQUESTED);
        return $meetsProtocolRequirement && $meetsPublicationStatusRequirement;
    }

    public function isPublishedToProduction()
    {
        return $this->status == DomainEntity::STATE_PUBLISHED && $this->environment == DomainEntity::ENVIRONMENT_PRODUCTION;
    }

    public function isPublished()
    {
        return $this->status === DomainEntity::STATE_PUBLISHED;
    }

    public function isRequested()
    {
        return $this->status === DomainEntity::STATE_PUBLICATION_REQUESTED;
    }

    public function isDeleteRequested()
    {
        return $this->status === DomainEntity::STATE_REMOVAL_REQUESTED;
    }
}
