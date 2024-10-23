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
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ChangeMembershipRoleException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ResendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\SendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\PublishEntityClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\TeamsClient;

class TeamsPublishEntityClientTest extends MockeryTestCase
{
    /**
     * @var PublishEntityClient
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $this->logger = m::mock(LoggerInterface::class);

        $this->client = new PublishEntityClient(
            new TeamsClient(
                $guzzle,
                new NullLogger()
            ),
            $this->logger
        );
    }

    /**
     * @throws ChangeMembershipRoleException
     */
    public function testItCanChangeMembership()
    {
        $this->mockHandler->append(new Response(201, [], ''));
        $response = $this->client->changeMembership(1, 'OWNER');

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @throws SendInviteException
     */
    public function testItCanInviteAMember()
    {
        $invite = [
            "teamId" => 2,
            "intendedRole" => "ADMIN",
            "emails" => [
                "test@test.org",
                "test2@test.org"
            ],
            "message" => "Please join",
            "language" => "ENGLISH"
        ];
        $this->mockHandler->append(new Response(201, [], ''));
        $response = $this->client->inviteMember($invite);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @throws ResendInviteException
     */
    public function testItCanResendAnInvitation()
    {
        $this->mockHandler->append(new Response(201, [], ''));
        $response = $this->client->resendInvitation(1, "Joske is nen koolmarchant");

        $this->assertEquals(201, $response->getStatusCode());
    }
}
