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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class QueryClientTest extends MockeryTestCase
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
            new HttpClient(
                $guzzle,
                new NullLogger()
            )
        );
    }

    public function test_it_can_see_if_entity_id_exists()
    {
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/query_response.json'))
            );
        $id = $this->client->findManageIdByEntityId('https://example.com/metadata');
        $this->assertEquals('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', $id);
    }

    public function test_it_can_query_existing_data()
    {
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/query_response.json'))
            );
        $response = $this->client->findByManageId('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33');
        $this->assertEquals('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', $response[0]['_id']);

        $this->assertEquals(
            'https://engine.dev.support.surfconext.nl/authentication/sp/metadata',
            $response[0]['data']['entityid']
        );
        $this->assertEquals(
            'OpenConext Engine',
            $response[0]['data']['metaDataFields']['name:en']
        );
    }

    public function test_it_can_query_non_existent_data()
    {
        // When the queried entityId does not exist, an empty array is returned
        $this->mockHandler->append(new Response(200, [], json_encode([])));
        $response = $this->client->findByManageId('does-not-exists');
        $this->assertEmpty($response);
    }

    public function test_it_can_query_xml_metadata()
    {
        $this->mockHandler->append(new Response(200, [], '<xml></xml>'));
        $response = $this->client->getMetadataXmlByManageId('manageid');
        $this->assertEquals($response, '<xml></xml>');
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException
     * @expectedExceptionMessage Unable to find entity with internal manage ID: "xyz"
     */
    public function test_it_handles_failing_query_action()
    {
        $this->mockHandler->append(new Response(418));
        $this->client->findByManageId('xyz');
    }
}
