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

use Exception;
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
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\HostBlocklistCheckerInterface;
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
     * @var HostBlocklistCheckerInterface|Mock
     */
    private $hostBlocklistChecker;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handler = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handler]);

        $this->client = $client;
        $this->logger = m::mock(LoggerInterface::class);
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('resolve')->andReturn(null)->byDefault();
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->andReturn(false)->byDefault();
        $this->fetcher = new Fetcher($this->client, $this->logger, 11, $this->hostBlocklistChecker);
    }

    public function test_it_can_fetch_xml_from_an_url()
    {
        $this->mockHandler->append(new Response(200, [], '<xml>'));
        $xml = $this->fetcher->fetch('https://www.ibuildings.nl/saml/metadata.xml');
        $this->assertEquals('<xml>', $xml);
    }

    public function test_it_blocks_a_url_that_resolves_to_a_blocked_host()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('resolve')->andReturn('169.254.169.254');
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->with('169.254.169.254')->andReturn(true);
        $this->fetcher = new Fetcher($this->client, $this->logger, 11, $this->hostBlocklistChecker);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata URL resolves to a blocked host', ['url' => 'http://169.254.169.254/metadata']);

        $this->expectExceptionMessage('Failed retrieving the metadata.');
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->fetcher->fetch('http://169.254.169.254/metadata');
    }

    public function test_it_resolves_the_host_exactly_once_and_reuses_it_for_the_pin()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('resolve')->once()->andReturn('93.184.216.34');
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->once()->with('93.184.216.34')->andReturn(false);
        $this->fetcher = new Fetcher($this->client, $this->logger, 11, $this->hostBlocklistChecker);

        $this->mockHandler->append(new Response(200, [], '<xml>'));
        $xml = $this->fetcher->fetch('https://www.ibuildings.nl/saml/metadata.xml');
        $this->assertEquals('<xml>', $xml);
    }

    public function test_it_validates_and_pins_every_redirect_hop()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('resolve')->once()->with('www.ibuildings.nl')->andReturn('93.184.216.34');
        $this->hostBlocklistChecker->shouldReceive('resolve')->once()->with('redirected.example.org')->andReturn('93.184.216.35');
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->with('93.184.216.34')->andReturn(false);
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->with('93.184.216.35')->andReturn(false);
        $this->fetcher = new Fetcher($this->client, $this->logger, 11, $this->hostBlocklistChecker);

        $this->mockHandler->append(new Response(301, ['Location' => 'https://redirected.example.org/metadata.xml']));
        $this->mockHandler->append(new Response(200, [], '<xml>'));

        $xml = $this->fetcher->fetch('https://www.ibuildings.nl/saml/metadata.xml');
        $this->assertEquals('<xml>', $xml);
    }

    public function test_it_blocks_a_redirect_that_resolves_to_a_blocked_host()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('resolve')->once()->with('www.ibuildings.nl')->andReturn('93.184.216.34');
        $this->hostBlocklistChecker->shouldReceive('resolve')->once()->with('169.254.169.254')->andReturn('169.254.169.254');
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->with('93.184.216.34')->andReturn(false);
        $this->hostBlocklistChecker->shouldReceive('isIpBlocked')->with('169.254.169.254')->andReturn(true);
        $this->fetcher = new Fetcher($this->client, $this->logger, 11, $this->hostBlocklistChecker);

        $this->mockHandler->append(new Response(302, ['Location' => 'http://169.254.169.254/metadata']));

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata URL resolves to a blocked host', ['url' => 'http://169.254.169.254/metadata']);

        $this->expectExceptionMessage('Failed retrieving the metadata.');
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->fetcher->fetch('https://www.ibuildings.nl/saml/metadata.xml');
    }

    public function test_it_rejects_an_ftp_scheme_url()
    {
        $this->hostBlocklistChecker->shouldNotReceive('resolve');
        $this->hostBlocklistChecker->shouldNotReceive('isIpBlocked');

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata URL uses an unsupported scheme', ['url' => 'ftp://www.ibuildings.nl/metadata.xml']);

        $this->expectExceptionMessage('Failed retrieving the metadata.');
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->fetcher->fetch('ftp://www.ibuildings.nl/metadata.xml');
    }

    public function test_it_allows_a_blocked_host_when_allow_metadata_private_hosts_is_true()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldNotReceive('isIpBlocked');
        $this->hostBlocklistChecker->shouldReceive('resolve')->andReturn(null)->byDefault();
        $this->fetcher = new Fetcher($this->client, $this->logger, 11, $this->hostBlocklistChecker, true);

        $this->mockHandler->append(new Response(200, [], '<xml>'));
        $xml = $this->fetcher->fetch('http://169.254.169.254/metadata');
        $this->assertEquals('<xml>', $xml);
    }

    public function test_it_handles_exceptions()
    {
        $exception = new Exception('');
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata exception', ['e' => $exception]);

        $this->expectExceptionMessage("Failed retrieving the metadata.");
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }

    public function test_it_handles_curl_ssl_authentication_error()
    {
        $this->expectExceptionMessage("Failed retrieving the metadata (SSL certificate cannot be authenticated - message:cURL error 60: Peer certificate cannot be authenticated with known CA certificates. (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)");
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $exceptionMessage = 'cURL error 60: Peer certificate cannot be authenticated with known CA certificates. (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)';
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
     *
     *
     *  The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
     */
    public function test_it_handles_curl_ssl_invalid_certificate_error()
    {
        $exceptionMessage = 'cURL error 51: The remote server\'s SSL certificate or SSH md5 fingerprint was deemed not OK.';
        $exception = new ConnectException(
            $exceptionMessage,
            new Request('GET', 'https://exapmle.com/foobar.xml')
        );
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata CURL exception', ['e' => $exception]);

        $this->expectExceptionMessage(
            "Failed retrieving the metadata (SSL certificate is not valid - message:cURL error 51: The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK."
        );
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }

    /**
     *
     *
     * server, and under the circumstances, getting nothing is considered an error.).
     */
    public function test_it_handles_curl_errors()
    {
        $exceptionMessage = 'cURL error 52: Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.';
        $exception = new ConnectException($exceptionMessage, new Request('GET', 'https://exapmle.com/foobar.xml'));
        $this->mockHandler->append($exception);

        $this->logger
            ->shouldReceive('info')
            ->with('Metadata CURL exception', ['e' => $exception]);

        $this->expectExceptionMessage(
            "Failed retrieving the metadata (message:cURL error 52: Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.)."
        );
        $this->expectException(\Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException::class);
        $this->fetcher->fetch('https://exapmle.com/foobar.xml');
    }
}
