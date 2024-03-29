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

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class EntityOidcConfirmation
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $entityId,
        private readonly string $clientSecret,
        string $protocol,
    ) {
        $supportedProtocols = [
            Constants::TYPE_OPENID_CONNECT_TNG,
            Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER,
            Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT,
        ];

        if (!in_array($protocol, $supportedProtocols)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Only use the EntityOidcConfirmation for one of these protocols %s',
                    implode(', ', $supportedProtocols)
                )
            );
        }
    }

    public static function fromEntity(ManageEntity $entity): self
    {
        return new self(
            $entity->getMetaData()->getEntityId(),
            $entity->getOidcClient()->getClientSecret(),
            $entity->getProtocol()->getProtocol()
        );
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return OidcngClientIdParser::parse($this->entityId);
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }
}
