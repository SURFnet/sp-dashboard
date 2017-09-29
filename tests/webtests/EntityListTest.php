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

namespace Surfnet\ServiceProviderDashboard\Webtests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EntityListTest extends WebTestCase
{
    public function test_entity_list()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $pageTitle = $crawler->filter('.page-container .card h2');

        $this->assertEquals('Services', $pageTitle->text());
    }
}
