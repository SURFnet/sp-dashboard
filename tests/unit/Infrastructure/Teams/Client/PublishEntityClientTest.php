<?php

/** Copyright 2021 SURFnet B.V.
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

namespace Infrastructure\Teams\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\PublishEntityClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGeneratorStrategy;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\TeamsClient;

class PublishEntityClientTest extends MockeryTestCase
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

    public function setUp()
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

    public function test_it_can_create_a_team()
    {
        $team = [
            "name" => "Champions ",
            "description" => "Team champions",
            "personalNote" => "Team created by SP Dashboard",
            "viewable" => true,
            "emails" => [
                "test@test.com" => "ADMIN"
            ],
            "roleOfCurrentUser" => "ADMIN",
            "invitationMessage" => "Please..",
            "language" => "DUTCH"
        ];
        $this->mockHandler->append(new Response(201, [], json_encode($team)));

        $json = file_get_contents(__DIR__ . '/fixture/team.json');

        $response = $this->client->createTeam($team);
        $this->assertEquals($json, $response['data']);
    }

    public function test_it_can_change_membership()
    {
        $this->mockHandler->append(new Response(201, [], '{"status":"OK"}'));

        $json = file_get_contents(__DIR__ . '/fixture/team.json');

        $response = $this->client->changeMembership(1, 'OWNER');
        $this->assertEquals('OK', $response['status']);
    }

    public function test_it_can_invite_a_member()
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
        $this->mockHandler->append(new Response(201, [], '{"status":"OK"}'));

        $response = $this->client->inviteMember($invite);
        $this->assertEquals('OK', $response['status']);
    }

    public function test_it_can_resend_an_invitation()
    {
        $this->mockHandler->append(new Response(201, [], '{"status":"OK"}'));

        $response = $this->client->resendInvitation(1, "Joske is nen koolmarchant");
        $this->assertEquals('OK', $response['status']);
    }
}
