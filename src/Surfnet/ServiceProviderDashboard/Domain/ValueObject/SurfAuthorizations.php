<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Domain\Exception\InvalidAuthorizationException;
use function preg_match;
use function reset;

class SurfAuthorizations
{
    private const SAB_ORGCODE_PATTERN = '/urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:(.+)/';
    private string $organizationCode;
    private bool $isSurfConextRepresentative;

    public function __construct(array $authorizations, string $sabRoleName)
    {
        // Is the ROLE_NAME present in the authorizations?
        $this->isSurfConextRepresentative = false;
        if (in_array($sabRoleName, $authorizations)) {
            $this->isSurfConextRepresentative = true;
        }

        // Now test if the organization code is present in the authorizations
        $match = preg_grep(self::SAB_ORGCODE_PATTERN, $authorizations);
        if ($match === false || count($match) !== 1) {
            throw new InvalidAuthorizationException('The organizationCode could not be found in the authorizations');
        }
        $codeAttributeValue = reset($match);
        $organizationCodeMatches = [];
        preg_match(self::SAB_ORGCODE_PATTERN, $codeAttributeValue, $organizationCodeMatches);
        if (!isset($organizationCodeMatches[1])) {
            throw new InvalidAuthorizationException('The organizationCode could not be extracted');
        }
        $this->organizationCode = $organizationCodeMatches[1];
    }

    public function isSurfConextRepresentative(): bool
    {
        return $this->isSurfConextRepresentative;
    }

    public function getOrganizationCode(): string
    {
        return $this->organizationCode;
    }
}
