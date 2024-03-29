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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Exception;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Exception\ProtocolNotFoundException;
use Webmozart\Assert\Assert;

class Protocol implements Comparable
{
    final public const SAML20_SP = 'saml20_sp';

    final public const OIDC10_RP = 'oidc10_rp';

    final public const OAUTH20_RS = 'oauth20_rs';

    private static array $protocolMapping = [
        self::SAML20_SP => Constants::TYPE_SAML,
        self::OIDC10_RP => Constants::TYPE_OPENID_CONNECT_TNG,
        self::OAUTH20_RS => Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER,
        // CC was already mapped to determine its entity type
        Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT => Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT,
    ];

    /**
     * @SuppressWarnings(PHPMD.UndefinedVariable) - protocolMapping is defined, md does not seem to resolve correctly
     */
    public static function fromApiResponse(string $manageProtocol): self
    {
        $protocol = self::$protocolMapping[$manageProtocol];
        return new self($protocol);
    }

    public function __construct(private ?string $protocol)
    {
        Assert::nullOrString($protocol);
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function merge(Protocol $protocol): void
    {
        $this->protocol = is_null($protocol->getProtocol()) ? null : $protocol->getProtocol();
    }

    public function asArray(): array
    {
        return [
            'type' => $this->getProtocol(),
        ];
    }

    /**
     * @throws Exception
     */
    public function getManagedProtocol(): string
    {
        /**
         * An exception to the rule on 'oauth20_ccc'
         */
        if ($this->protocol === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT) {
            return self::OIDC10_RP;
        }

        if (in_array($this->protocol, self::$protocolMapping)) {
            return array_search($this->protocol, self::$protocolMapping);
        }

        throw new ProtocolNotFoundException(sprintf('The protocol \'%s\' is not supported', $this->protocol));
    }
}
