<?php

/**
 * Copyright 2019 SURFnet B.V.
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

class EntityOidcConfirmation
{
    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @param string $entityId
     * @param string $clientSecret
     */
    public function __construct(
        $entityId,
        $clientSecret
    ) {
        $this->entityId = $entityId;
        $this->clientSecret = $clientSecret;
    }

    public static function fromEntity(DomainEntity $entity)
    {
        return new self(
            $entity->getEntityId(),
            $entity->getClientSecret()
        );
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return str_replace('://', '@//', $this->entityId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }
}
