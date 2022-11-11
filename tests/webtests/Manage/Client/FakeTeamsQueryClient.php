<?php

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryTeamsRepository;

class FakeTeamsQueryClient implements QueryTeamsRepository
{
    private $data = [];

    public function registerTeam(
        string $urn,
        string $data
    ) {
        $this->data[$urn] = [$urn, $data];
    }

    public function findTeamByUrn(string $urn): ?array
    {
        if (array_key_exists($urn, $this->data)) {
            return $this->data[$urn];
        }
        return null;
    }
}
