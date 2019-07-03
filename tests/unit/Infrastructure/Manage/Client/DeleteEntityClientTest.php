<?php

/**
 * Copyright 2018 SURFnet B.V.
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
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\DeleteEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class DeleteEntityClientTest extends MockeryTestCase
{
    /**
     * @var DeleteEntityClient
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

    /**
     * @var GeneratorInterface|Mock
     */
    private $generator;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $this->generator = m::mock(GeneratorInterface::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->client = new DeleteEntityClient(
            new HttpClient(
                $guzzle,
                new NullLogger()
            ),
            $this->logger
        );
    }

    public function test_it_can_delete_an_entity()
    {
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/delete_response_success.json'))
            );
        $response = $this->client->delete('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', Entity::TYPE_SAML);
        $this->assertEquals(DeleteEntityRepository::RESULT_SUCCESS, $response);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException
     * @expectedExceptionMessage Not allowed to delete entity with internal manage ID
     */
    public function test_it_can_handle_error_response()
    {
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/delete_response_failure.json'))
            );
        $this->client->delete('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', Entity::TYPE_OPENID_CONNECT_TNG);
    }
}
