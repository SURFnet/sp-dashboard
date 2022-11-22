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
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\DeleteEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\TeamsClient;
use function file_get_contents;

class TeamsDeleteEntityClientTest extends MockeryTestCase
{
    /**
     * @var DeleteEntityClient
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $logger = m::mock(LoggerInterface::class);

        $this->client = new DeleteEntityClient(
            new TeamsClient(
                $guzzle,
                new NullLogger()
            ),
            $logger
        );
    }

    public function test_it_can_delete_a_team()
    {
        $this->mockHandler->append(new Response(201, [], ''));
        $response = $this->client->deleteTeam(1);

        $this->assertEquals('success', $response);
    }

    public function test_it_can_delete_a_member()
    {
        $this->mockHandler->append(new Response(201, [], ''));
        $response = $this->client->deleteMembership(1);

        $this->assertEquals('success', $response);
    }
}
