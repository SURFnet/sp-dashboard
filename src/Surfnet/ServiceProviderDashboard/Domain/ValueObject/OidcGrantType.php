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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

use InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;

class OidcGrantType
{
    private static array $validGrantTypes = [
        Constants::GRANT_TYPE_AUTHORIZATION_CODE,
        Constants::GRANT_TYPE_IMPLICIT,
        Constants::GRANT_TYPE_CLIENT_CREDENTIALS,
    ];

    private string $grantType;

    public function __construct(
        string $grantType = Constants::GRANT_TYPE_AUTHORIZATION_CODE,
    ) {
        if (!in_array($grantType, self::$validGrantTypes)) {
            throw new InvalidArgumentException("invalid grant type");
        }

        $this->grantType = $grantType;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }
}
