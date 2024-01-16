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

namespace Surfnet\ServiceProviderDashboard\Application\Parser;

/**
 * A SAML Entity ID to OIDC ClientID (TNG) parser
 *
 * Converts a entity id (which is a valid URL) to the proprietary SURFnet OpenID connect client id format.
 *
 * https://www.pivotaltracker.com/story/show/166702113/comments/204130376
 */
class OidcngClientIdParser
{
    /**
     * @param string $entityId
     * @return string
     */
    public static function parse($entityId)
    {
        $parts = parse_url($entityId);

        // If no scheme is set, we are dealing with an entity that already had his scheme chopped off
        if (!isset($parts['scheme'])) {
            return $entityId;
        }

        // Remove the scheme (protocol) and the :// part
        $clientId = str_replace($parts['scheme'].'://', '', $entityId);

        // If a trailing slash is present, stripit!
        if (str_ends_with($clientId, '/')) {
            $clientId = substr($clientId, 0, -1);
        }

        return $clientId;
    }
}
