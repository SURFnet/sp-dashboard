<?php

/**
 * Copyright 2021 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\ManageClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\QueryClient;
use function file_get_contents;

class TeamsQueryClientTest extends MockeryTestCase
{
    /**
     * @var QueryClient
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);
        $this->client = new QueryClient(
            new ManageClient(
                $guzzle,
                new NullLogger()
            )
        );
    }

    public function test_it_can_find_a_team_by_urn()
    {
        $json = file_get_contents(__DIR__ . '/fixture/team.json');
        $this->mockHandler->append(new Response(201, [], $json));
        $teamInfo = json_decode(file_get_contents(__DIR__ . '/fixture/query_response.json'), true);

        $response = $this->client->findTeamByUrn('demo:openconext:org:champions');
        $this->assertEquals($teamInfo, $response);
    }
}
