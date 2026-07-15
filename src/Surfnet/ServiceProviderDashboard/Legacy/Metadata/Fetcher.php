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

namespace Surfnet\ServiceProviderDashboard\Legacy\Metadata;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\HostBlocklistCheckerInterface;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Exception;

class Fetcher implements FetcherInterface
{
    private const MAX_REDIRECTS = 5;

    private readonly int $timeout;

    private static string $curlErrorRegex = '/cURL error (\d+):/';

    public function __construct(
        private readonly ClientInterface $guzzle,
        private readonly LoggerInterface $logger,
        $timeout,
        private readonly HostBlocklistCheckerInterface $hostBlocklistChecker,
        private readonly bool $allowMetadataPrivateHosts = false,
        private readonly bool $verifySsl = true,
    ) {
        $this->timeout = (int) $timeout;
    }

    /**
     * @param string $url
     * @throws MetadataFetchException
     */
    public function fetch($url): string
    {
        try {
            return $this->fetchFollowingRedirects($url)->getBody()->getContents();
        } catch (MetadataFetchException $e) {
            throw $e;
        } catch (ConnectException $e) {
            $this->logger->info('Metadata CURL exception', ['e' => $e]);
            $curlError = ' (' . $this->getCurlErrorDescription($e->getMessage()) . ').';
            throw new MetadataFetchException('Failed retrieving the metadata' . $curlError);
        } catch (Exception $e) {
            $this->logger->info('Metadata exception', ['e' => $e]);
            throw new MetadataFetchException('Failed retrieving the metadata.');
        }
    }

    private function fetchFollowingRedirects(string $url): ResponseInterface
    {
        for ($redirectCount = 0; $redirectCount <= self::MAX_REDIRECTS; $redirectCount++) {
            $ip = $this->guardAgainstBlockedHost($url);
            $response = $this->guzzle->request('GET', $url, $this->buildGuzzleOptions($url, $ip));

            if (!$this->isRedirect($response)) {
                return $response;
            }

            $url = (string) UriResolver::resolve(Utils::uriFor($url), Utils::uriFor($response->getHeaderLine('Location')));
        }

        throw new MetadataFetchException('Failed retrieving the metadata (too many redirects).');
    }

    private function isRedirect(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        return $statusCode >= 300 && $statusCode < 400 && $response->hasHeader('Location');
    }

    private function guardAgainstBlockedHost(string $url): ?string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            $this->logger->info('Metadata URL uses an unsupported scheme', ['url' => $url]);
            throw new MetadataFetchException('Failed retrieving the metadata.');
        }

        $host = parse_url($url, PHP_URL_HOST);
        $ip = $host !== null && $host !== false ? $this->hostBlocklistChecker->resolve($host) : null;

        if (!$this->allowMetadataPrivateHosts && $this->hostBlocklistChecker->isIpBlocked($ip)) {
            $this->logger->info('Metadata URL resolves to a blocked host', ['url' => $url]);
            throw new MetadataFetchException('Failed retrieving the metadata.');
        }

        return $ip;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGuzzleOptions(string $url, ?string $ip): array
    {
        $guzzleOptions = [
            'timeout' => $this->timeout,
            'verify' => $this->verifySsl,
            'allow_redirects' => false,
            'curl' => [
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            ],
        ];

        $host = parse_url($url, PHP_URL_HOST);
        if ($ip !== null && $host !== null && $host !== false) {
            $port = parse_url($url, PHP_URL_PORT)
                ?? (strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https' ? 443 : 80);
            $guzzleOptions['curl'][CURLOPT_RESOLVE] = ["$host:$port:$ip"];
        }

        return $guzzleOptions;
    }

    private function getCurlErrorDescription(string $message): string
    {
        $error = '';
        $errorNumber = $this->extractErrorNumber($message);
        switch ($errorNumber) {
            case 51:
                $error = 'SSL certificate is not valid';
                break;
            case 60:
                $error = 'SSL certificate cannot be authenticated';
                break;
        }

        if ($error !== '' && $error !== '0') {
            $error .= ' - ';
        }

        return $error . 'message:' . $message;
    }

    private function extractErrorNumber(string $message)
    {
        $matches = [];
        preg_match(self::$curlErrorRegex, (string) $message, $matches);
        if (is_numeric($matches[1])) {
            return $matches[1];
        }
        return $message;
    }
}
