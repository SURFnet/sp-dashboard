<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class IdentityProviderClientTest extends MockeryTestCase
{
    /**
     * @var IdentityProviderClient
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var Config&Mock
     */
    private $config;

    public function setUp()
    {
        $this->config = m::mock(Config::class);
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);
        $this->client = new IdentityProviderClient(
            new HttpClient(
                $guzzle,
                new NullLogger()
            ),
            $this->config
        );
    }

    public function test_it_can_return_all_published_idps()
    {
        // When the queried entityId is found
        $this->mockHandler
            ->append(
                new Response(200, [], file_get_contents(__DIR__ . '/fixture/identity_provider_response.json'))
            );

        $this->config
            ->shouldReceive('getPublicationStatus->getStatus')
            ->andReturn('testaccepted');

        $idps = $this->client->findAll();
        $this->assertCount(4, $idps);

        $this->assertInstanceOf(IdentityProvider::class, $idps[0]);
        $this->assertSame('http://mock-idp', $idps[0]->getEntityId());
        $this->assertSame('bfe8f00d-317a-4fbc-9cf8-ad2f3b2af578', $idps[0]->getManageId());
        $this->assertSame('OpenConext Mujina IDP EN', $idps[0]->getNameEn());
        $this->assertSame('OpenConext Mujina IDP NL', $idps[0]->getNameNl());
        $this->assertSame('OpenConext Mujina IDP NL', $idps[0]->getName());

        $this->assertInstanceOf(IdentityProvider::class, $idps[1]);
        $this->assertSame('https://engine.dev.support.surfconext.nl/authentication/idp/metadata', $idps[1]->getEntityId());
        $this->assertSame('0c3febd2-3f67-4b8a-b90d-ce56a3b0abb4', $idps[1]->getManageId());
        $this->assertSame('OpenConext Engine EN', $idps[1]->getNameEn());
        $this->assertSame('', $idps[1]->getNameNl());
        $this->assertSame('OpenConext Engine EN', $idps[1]->getName());

        $this->assertInstanceOf(IdentityProvider::class, $idps[2]);
        $this->assertSame('https://engine.dev.support.surfconext.nl/authentication/idp/metadata2', $idps[2]->getEntityId());
        $this->assertSame('0c3febd2-3f67-4b8a-b90d-ce56a3b0abb5', $idps[2]->getManageId());
        $this->assertSame(' ', $idps[2]->getNameEn());
        $this->assertSame('OpenConext Engine 2 NL', $idps[2]->getNameNl());
        $this->assertSame('OpenConext Engine 2 NL', $idps[2]->getName());

        $this->assertInstanceOf(IdentityProvider::class, $idps[3]);
        $this->assertSame('https://engine.dev.support.surfconext.nl/authentication/idp/metadata2', $idps[3]->getEntityId());
        $this->assertSame('0c3febd2-3f67-4b8a-b90d-ce56a3b0abb6', $idps[3]->getManageId());
        $this->assertSame('OpenConext Engine 3 EN', $idps[3]->getNameEn());
        $this->assertSame('', $idps[3]->getNameNl());
        $this->assertSame('OpenConext Engine 3 EN', $idps[3]->getName());
    }
}
