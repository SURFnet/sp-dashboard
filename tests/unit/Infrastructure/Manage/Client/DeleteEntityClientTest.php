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
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\DeleteManageEntityClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\ManageClient;
use function file_get_contents;

class DeleteEntityClientTest extends MockeryTestCase
{
    /**
     * @var DeleteManageEntityClient
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

    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $this->generator = m::mock(GeneratorInterface::class);

        $this->logger = m::mock(LoggerInterface::class);

        $this->client = new DeleteManageEntityClient(
            new ManageClient(
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
        $response = $this->client->delete('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', Constants::TYPE_SAML);
        $this->assertEquals(DeleteManageEntityRepository::RESULT_SUCCESS, $response);
    }

    public function test_it_can_handle_error_response()
    {
        $this->expectException(UnableToDeleteEntityException::class);
        $this->expectExceptionMessage("Not allowed to delete entity with internal manage ID");
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/delete_response_failure.json'))
            );
        $this->client->delete('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', Constants::TYPE_OPENID_CONNECT_TNG);
    }

    /**
     * @dataProvider provideInvalidProtocols
     */
    public function test_it_maps_enitty_types_correctly($invalidType)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The protocol "%s" can not be mapped to a manage entity type', $invalidType)
        );
        $this->client->delete('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', $invalidType);
    }

    public function provideInvalidProtocols()
    {
        yield ['saml20_sp'];
        yield ['oicd_ccc'];
        yield ['oauth_ccc'];
        yield ['i-dont-exist'];
    }

    /**
     * @dataProvider provideExpectedProtocolMappings
     */
    public function test_it_maps_enitty_types_correctly_with_existing_protocols($inputProtocol, $expectedProtocol)
    {
        $client = m::mock(HttpClientInterface::class);
        $this->client = new DeleteManageEntityClient(
            $client,
            $this->logger
        );

        $manageEntityId = 'db2e5c63-3c54-4962-bf4a-d6ced1e9cf33';
        $client
            ->shouldReceive('delete')
            ->with(sprintf('/manage/api/internal/metadata/%s/%s', $expectedProtocol, $manageEntityId))
            ->once()
            ->andReturn(true);

        $this->client->delete($manageEntityId, $inputProtocol);
    }

    public function provideExpectedProtocolMappings()
    {
        yield ['oauth20_rs', 'oauth20_rs'];
        yield ['oidcng', 'oidc10_rp'];
        yield ['oauth20_ccc', 'oidc10_rp'];
        yield ['saml20', 'saml20_sp'];
    }
}
