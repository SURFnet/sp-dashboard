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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;

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

    public function __construct(
        string $id,
        int $serviceId,
        string $status,
        string $environment,
        string $protocol,
        bool $isReadOnly
    ) {
    
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

    public function allowEditAction(): bool
    {
        $notEditable =
            $this->readOnly
            || $this->status === Constants::STATE_REMOVAL_REQUESTED;

        if ($notEditable) {
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
        $isPublishedTestEntity = ($this->status == Constants::STATE_PUBLISHED
            && $this->environment == Constants::ENVIRONMENT_TEST);

        $isPublishedProdEntity = ($this->status == Constants::STATE_PUBLICATION_REQUESTED
            && $this->environment == Constants::ENVIRONMENT_PRODUCTION);

        return $isPublishedTestEntity || $isPublishedProdEntity;
    }

    public function allowCopyToProductionAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED && $this->environment == Constants::ENVIRONMENT_TEST;
    }

    public function allowCloneAction()
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED && $this->environment == Constants::ENVIRONMENT_PRODUCTION;
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
        return $this->status == Constants::STATE_PUBLISHED &&
            $this->environment == Constants::ENVIRONMENT_TEST &&
            $this->protocol !== Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER &&
            $this->protocol !== Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT;
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
        $meetsProtocolRequirement = $protocol == Constants::TYPE_OPENID_CONNECT_TNG ||
            $protocol == Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER ||
            $protocol == Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT;
        $meetsPublicationStatusRequirement = ($status == Constants::STATE_PUBLISHED ||
            $status == Constants::STATE_PUBLICATION_REQUESTED);
        return $meetsProtocolRequirement && $meetsPublicationStatusRequirement;
    }

    public function allowChangeRequestAction(): bool
    {
        if ($this->readOnly || $this->environment !== Constants::ENVIRONMENT_PRODUCTION) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED;
    }

    public function allowOpenConnectionRequestAction(): bool
    {
        if ($this->readOnly || $this->environment !== Constants::ENVIRONMENT_PRODUCTION) {
            return false;
        }
        $protocol = $this->protocol;
        $meetsProtocolRequirement = $protocol == Constants::TYPE_SAML ||
            $protocol == Constants::TYPE_OPENID_CONNECT_TNG;

        return $meetsProtocolRequirement && $this->status == Constants::STATE_PUBLISHED;
    }

    public function isPublishedToProduction()
    {
        return $this->status == Constants::STATE_PUBLISHED && $this->environment == Constants::ENVIRONMENT_PRODUCTION;
    }

    public function isPublished()
    {
        return $this->status === Constants::STATE_PUBLISHED;
    }

    public function isRequested()
    {
        return $this->status === Constants::STATE_PUBLICATION_REQUESTED;
    }

    public function isDeleteRequested()
    {
        return $this->status === Constants::STATE_REMOVAL_REQUESTED;
    }
}
