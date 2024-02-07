<?php

declare(strict_types = 1);

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
    public function __construct(
        private readonly string $id,
        private readonly int $serviceId,
        private string $status,
        private readonly string $environment,
        private string $protocol,
        private readonly bool $readOnly,
        private readonly bool $changeRequest,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function allowEditAction(): bool
    {
        $notEditable =
            $this->readOnly
            || $this->status === Constants::STATE_REMOVAL_REQUESTED;
        return !$notEditable;
    }

    public function allowCopyAction(): bool
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

    public function allowCopyToProductionAction(): bool
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED && $this->environment == Constants::ENVIRONMENT_TEST;
    }

    public function allowCloneAction(): bool
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED && $this->environment == Constants::ENVIRONMENT_PRODUCTION;
    }

    public function allowDeleteAction(): bool
    {
        if ($this->readOnly) {
            return false;
        }
        return !$this->isDeleteRequested();
    }

    public function allowAclAction(): bool
    {
        if ($this->readOnly) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED &&
            $this->environment == Constants::ENVIRONMENT_TEST &&
            $this->protocol !== Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER &&
            $this->protocol !== Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT;
    }

    public function allowSecretResetAction(): bool
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
        if ($this->readOnly || $this->environment !== Constants::ENVIRONMENT_PRODUCTION || !$this->changeRequest) {
            return false;
        }
        return $this->status == Constants::STATE_PUBLISHED;
    }

    public function allowCreateConnectionRequestAction(): bool
    {
        if ($this->readOnly || $this->environment !== Constants::ENVIRONMENT_PRODUCTION) {
            return false;
        }
        $protocol = $this->protocol;
        $meetsProtocolRequirement = $protocol == Constants::TYPE_SAML ||
            $protocol == Constants::TYPE_OPENID_CONNECT_TNG;

        return $meetsProtocolRequirement && $this->status == Constants::STATE_PUBLISHED;
    }

    public function isPublishedToProduction(): bool
    {
        return $this->status == Constants::STATE_PUBLISHED && $this->environment == Constants::ENVIRONMENT_PRODUCTION;
    }

    public function isPublished(): bool
    {
        return $this->status === Constants::STATE_PUBLISHED;
    }

    public function isRequested(): bool
    {
        return $this->status === Constants::STATE_PUBLICATION_REQUESTED;
    }

    public function isDeleteRequested(): bool
    {
        return $this->status === Constants::STATE_REMOVAL_REQUESTED;
    }
}
