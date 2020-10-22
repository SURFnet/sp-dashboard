<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use RuntimeException;
use function json_decode;

class ClientResultRaw implements ClientResultInterface
{
    private $json;

    public function __construct($json)
    {
        $this->json = $json;
    }

    public function getEntityResult(): array
    {
        return json_decode($this->json, true);
    }

    public function getSearchResult(): array
    {
        throw new RuntimeException('Search results are not supported in ClientResultRaw');
    }

    public function getId(): string
    {
        $data = json_decode($this->json, true);
        return $data['id'];
    }

    public function getEntityId(): string
    {
        $data = json_decode($this->json, true);
        return $data['data']['entityid'];
    }
}
