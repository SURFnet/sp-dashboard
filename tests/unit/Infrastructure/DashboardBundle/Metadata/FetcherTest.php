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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Metadata;

use GuzzleHttp\ClientInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Metadata\Fetcher;

class FetcherTest extends MockeryTestCase
{
    /** @var Fetcher */
    private $fetcher;

    /**
     * @var ClientInterface|Mock
     */
    private $client;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {
        $this->client = m::mock(ClientInterface::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->fetcher = new Fetcher($this->client, $this->logger, 11);
    }

    public function test_it_can_fetch_xml_from_an_url()
    {
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $response
            ->shouldReceive('getBody')
            ->andReturn($stream);

        $stream
            ->shouldReceive('getContents')
            ->andReturn('<xml>');

        $this->client
            ->shouldReceive('request')
            ->with(
                'GET',
                'https://www.ibuildings.nl/saml/metadata.xml',
                [ 'timeout' => 11, 'verify' => false ]
            )
            ->andReturn($response);

        $xml = $this->fetcher->fetch('https://www.ibuildings.nl/saml/metadata.xml');
        $this->assertEquals('<xml>', $xml);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed retrieving the metadata.
     */
    public function test_it_handles_exceptions()
    {
        $exception = m::mock(\Exception::class);
        $this->client
            ->shouldReceive('request')
            ->andThrow($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata exception', ['e' => $exception]);

        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }
}
