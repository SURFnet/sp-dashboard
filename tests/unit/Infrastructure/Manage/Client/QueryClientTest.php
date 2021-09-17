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

namespace Infrastructure\Manage\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\ManageClient;
use function file_get_contents;

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
            new ManageClient(
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
                new Response(200, [], '[]'),
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/query_response.json')),
                new Response(200, [], '[]')
            );
        $id = $this->client->findManageIdByEntityId('https://example.com/metadata');
        $this->assertEquals('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', $id);
    }

    public function test_it_can_query_existing_data()
    {
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], '[]'),
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/read_response.json')),
                new Response(200, [], '[]')
            );
        $response = $this->client->findByManageId('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33');
        $this->assertEquals('db2e5c63-3c54-4962-bf4a-d6ced1e9cf33', $response->getId());

        $this->assertEquals(
            'https://engine.dev.support.surfconext.nl/authentication/sp/metadata',
            $response->getMetaData()->getEntityId()
        );
        $this->assertEquals(
            'OpenConext Engine',
            $response->getMetaData()->getNameEn()
        );
    }


    /**
     * Test, when multiple entities are retrieved from manage search from both the SAML and the OIDC endpoints, no
     * overwriting is applied to the merged search results.
     *
     * @see https://www.pivotaltracker.com/story/show/168834919
     */
    public function test_search_does_not_override_keys()
    {
        $this->mockHandler
            ->append(
                # The saml search endpoint is queried
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/search_saml.json')
                ),
                # The oidc search endpoint is queried
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/search_oidc.json')
                ),
                # The oauth search endpoint is queried
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/search_oauth.json')
                ),
                # Next the oidc entities are retrieved from manage by id, first trying the SAML endpoint, then OIDC
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/read_response_oidc1.json')
                ),
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/read_response_oidc2.json')
                ),
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/read_response_oidc3.json')
                ),
                # The SAML entities are loaded
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/read_response_saml1.json')
                ),
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/read_response_saml2.json')
                ),
                # The oauth20_rs entities are loaded
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__.'/fixture/search_result_overwrite_bug/read_response_oauth.json')
                )
            );
        $response = $this->client->findByTeamName('team-UU', 'prodaccepted');

        $this->assertEquals('oidcng', $response[0]->getProtocol()->getProtocol());
        $this->assertEquals('oidcng', $response[1]->getProtocol()->getProtocol());
        $this->assertEquals('oidcng', $response[2]->getProtocol()->getProtocol());
        $this->assertEquals('saml20', $response[3]->getProtocol()->getProtocol());
        $this->assertEquals('saml20', $response[4]->getProtocol()->getProtocol());
        $this->assertEquals('oauth20_rs', $response[5]->getProtocol()->getProtocol());

        $this->assertCount(6, $response);
    }

    public function test_it_can_query_non_existent_data()
    {
        // When the queried entityId does not exist, an empty array is returned
        $this->mockHandler->append(
            new Response(200, [], '[]'),
            new Response(200, [], '[]'),
            new Response(200, [], '[]')
        );
        $response = $this->client->findByManageId('does-not-exists');
        $this->assertEmpty($response);
    }

    public function test_it_can_query_xml_metadata()
    {
        $this->mockHandler->append(new Response(200, [], '<xml></xml>'));
        $response = $this->client->getMetadataXmlByManageId('manageid');
        $this->assertEquals($response, '<xml></xml>');
    }

    public function test_it_handles_failing_query_action()
    {
        $this->expectExceptionMessage("Unable to find entity with internal manage ID: \"xyz\"");
        $this->expectException(\Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException::class);
        $this->mockHandler->append(new Response(418));
        $this->client->findByManageId('xyz');
    }
}
