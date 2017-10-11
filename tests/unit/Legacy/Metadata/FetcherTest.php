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

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Fetcher;

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

    /**
     * @var MockHandler
     */
    private $mockHandler;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $handler = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handler]);

        $this->client = $client;
        $this->logger = m::mock(LoggerInterface::class);
        $this->fetcher = new Fetcher($this->client, $this->logger, 11);
    }

    public function test_it_can_fetch_xml_from_an_url()
    {
        $this->mockHandler->append(new Response(200, [], '<xml>'));
        $xml = $this->fetcher->fetch('https://www.ibuildings.nl/saml/metadata.xml');
        $this->assertEquals('<xml>', $xml);
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed retrieving the metadata.
     */
    public function test_it_handles_exceptions()
    {
        $exception = new \Exception('');
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata exception', ['e' => $exception]);

        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed retrieving the metadata (SSL certificate cannot be authenticated - message:
     *  cURL error 60: Peer certificate cannot be authenticated with known CA certificates.)
     *  (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
     */
    public function test_it_handles_curl_ssl_authentication_error()
    {
        $exceptionMessage = 'cURL error 60: Peer certificate cannot be authenticated with known CA certificates. 
                             (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)';
        $exception = new ConnectException(
            $exceptionMessage,
            new Request('GET', 'https://exapmle.com/foobar.xml')
        );
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata CURL exception', ['e' => $exception]);

        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed retrieving the metadata (SSL certificate is not valid - message:
     *  The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
     */
    public function test_it_handles_curl_ssl_invalid_certificate_error()
    {
        $exceptionMessage = 'cURL error 51: The remote server\'s SSL certificate or SSH md5 fingerprint was deemed not 
                             OK.';
        $exception = new ConnectException(
            $exceptionMessage,
            new Request('GET', 'https://exapmle.com/foobar.xml')
        );
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata CURL exception', ['e' => $exception]);

        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed retrieving the metadata (message:cURL error 52: Nothing was returned from the
     * server, and under the circumstances, getting nothing is considered an error.).
     */
    public function test_it_handles_curl_errors()
    {
        $exceptionMessage = 'cURL error 52: Nothing was returned from the server, and under the circumstances, getting 
                             nothing is considered an error.';
        $exception = new ConnectException($exceptionMessage, new Request('GET', 'https://exapmle.com/foobar.xml'));
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata CURL exception', ['e' => $exception]);

        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }
}
