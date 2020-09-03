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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Webmozart\Assert\Assert;

class Protocol
{
    const SAML20_SP = 'saml20_sp';

    const OIDC10_RP = 'oidc10_rp';

    private static $protocolMapping = [
        self::SAML20_SP => Constants::TYPE_SAML,
        self::OIDC10_RP => Constants::TYPE_OPENID_CONNECT_TNG
    ];

    private $protocol;

    /**
     * @param array $data
     * @param string $manageProtocol
     * @return Protocol
     * @SuppressWarnings(PHPMD.UndefinedVariable) - protocolMapping is defined, md does not seem to resolve correctly
     */
    public static function fromApiResponse(array $data, $manageProtocol)
    {
        $protocol = self::$protocolMapping[$manageProtocol];

        // The old/first oidc implementation piggy backs on the saml20 entity, and is identified as oidc by the
        // oidcClient coin.
        $oidcClient = isset($data['data']['oidcClient']);
        if ($oidcClient && $protocol === Constants::TYPE_SAML) {
            $protocol = Constants::TYPE_OPENID_CONNECT;
        }

        $isResourceServer = isset($data['data']['metaDataFields']['isResourceServer']) && $data['data']['metaDataFields']['isResourceServer'];
        if ($protocol === Constants::TYPE_OPENID_CONNECT_TNG && $isResourceServer) {
            $protocol = Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER;
        }

        return new self($protocol);
    }

    /**
     * @param string $protocol
     */
    public function __construct(?string $protocol)
    {
        Assert::nullOrString($protocol);

        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
    public function merge(?Protocol $lprotocol)
    {
        if ($lprotocol === null) {
            $this->protocol = null;
            return;
        }
        $this->protocol = is_null($lprotocol->getProtocol()) ? null : $lprotocol->getProtocol();
    }
}
