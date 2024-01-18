<?php

//declare(strict_types = 1);

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
 * Parses a Manage oidc10_rp client id to a SPD client id
 *
 * Turns www.example.com into https://www.example.com
 *
 * In manage, the client id is stripped of the protocol (see OidcngClientIdParser). But when we are creating a
 * production copy of an entity, we again need the protocol in order to pass SPD form validation.
 *
 * https://www.pivotaltracker.com/story/show/166702113/comments/204130376
 */
class OidcngSpdClientIdParser
{
    /**
     * @param  string $entityId
     * @return string
     */
    public static function parse($entityId): string
    {
        $clientIdFormat = "https://%s";

        return sprintf($clientIdFormat, $entityId);
    }
}
