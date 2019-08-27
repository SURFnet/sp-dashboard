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

class OidcClientIdParser
{

    /**
     * Takes the entity id and removes the protocol as per:
     *
     * https://www.pivotaltracker.com/story/show/166702113/comments/204130376
     *
     * @param $entityId
     * @return mixed
     */
    public static function parse($entityId)
    {
        return str_replace('://', '@//', $entityId);
    }
}
