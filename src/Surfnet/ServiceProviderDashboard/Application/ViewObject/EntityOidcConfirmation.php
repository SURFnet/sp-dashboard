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
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcClientIdParser;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
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
     * @var string
     */
    private $protocol;

    /**
     * @param string $entityId
     * @param string $clientSecret
     * @param string $protocol
     * @throws InvalidArgumentException
     */
    public function __construct(
        $entityId,
        $clientSecret,
        $protocol
    ) {
        $supportedProtocols = [
            Constants::TYPE_OPENID_CONNECT,
            Constants::TYPE_OPENID_CONNECT_TNG,
            Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER
        ];

        if (!in_array($protocol, $supportedProtocols)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Only use the EntityOidcConfirmation for one of these protocols %s',
                    implode(', ', $supportedProtocols)
                )
            );
        }
        $this->entityId = $entityId;
        $this->clientSecret = $clientSecret;
        $this->protocol = $protocol;
    }

    public static function fromEntity(DomainEntity $entity)
    {
        return new self(
            $entity->getEntityId(),
            $entity->getClientSecret(),
            $entity->getProtocol()
        );
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        $isOidcng = $this->protocol === Constants::TYPE_OPENID_CONNECT_TNG ||
            $this->protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
        if ($isOidcng) {
            return OidcngClientIdParser::parse($this->entityId);
        }

        return OidcClientIdParser::parse($this->entityId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }
}
