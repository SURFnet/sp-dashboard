<?php

/**
 * Copyright 2021 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\AccessDeniedException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\MalformedResponseException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\UndeleteableResourceException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\UnreadableResourceException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InvalidJsonException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use function strtolower;

class HttpClient implements HttpClientInterface
{
    const TEST_API_NAME = 'testApi';
    const MODE_TEST = 'test';

    /**
     * @param string $mode The mode is used mainly for logging purposes, stating which environment was targeted.
     */
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiName = self::TEST_API_NAME,
        private readonly string $mode = self::MODE_TEST
    ) {
    }

    /**
     * @param string $path A URL path, optionally containing printf parameters.
     * The parameters will be URL encoded and formatted into the path string.
     * Example: "connections/%d.json"
     *
     * @return mixed $data
     *
     * @throws AccessDeniedException
     * @throws UnreadableResourceException
     * @throws MalformedResponseException
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function read(string $path, array $parameters = [], array $headers = ['Content-Type' => 'application/json'])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);
        $this->logger->debug(
            sprintf('Getting resource %s from %s (%s)', $resource, $this->apiName, $this->mode)
        );

        return $this->request('GET', $resource, [
            'exceptions' => false,
            'headers' => $headers
        ], function ($statusCode, $body, $method, $resource, $headers) {
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new UnreadableResourceException(
                    sprintf('Resource could not be read (status code %d)', $statusCode)
                );
            }

            if ((isset($headers['Content-Type'])) &&
                ($headers['Content-Type'] === 'application/json')) {
                return $this->parseResponse($body, $method, $resource);
            }

            if ((isset($headers['Content-Type'])) &&
                ($headers['Content-Type'] === 'application/xml')) {
                return $body;
            }
        });
    }

    /**
     * @param mixed $data
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws GuzzleException
     * @throws MalformedResponseException
     * @throws RuntimeException
     * @throws UnreadableResourceException
     */
    public function post(
        $data,
        string $path,
        array $parameters = [],
        array $headers = ['Content-Type' => 'application/json']
    ) {
        $resource = ResourcePathFormatter::format($path, $parameters);
        $this->logger->debug(
            sprintf('Posting data to %s (%s) on path %s', $this->apiName, $this->mode, $resource),
            ['data' => $data]
        );

        return $this->request('POST', $resource, [
            'exceptions' => false,
            'body' => $data,
            'headers' => $headers
        ]);
    }

    /**
     * @param mixed $data
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws GuzzleException
     * @throws MalformedResponseException
     * @throws RuntimeException
     * @throws UndeleteableResourceException
     */
    public function put(
        $data,
        string $path,
        array $parameters = [],
        array $headers = ['Content-Type' => 'application/json']
    ) {
        $resource = ResourcePathFormatter::format($path, $parameters);
        $this->logger->debug(
            sprintf('Putting data to %s (%s) on path %s', $this->apiName, $this->mode, $resource),
            ['data' => $data]
        );

        return $this->request('PUT', $resource, [
            'exceptions' => false,
            'body' => $data,
            'headers' => $headers
        ]);
    }

    /**
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws GuzzleException
     * @throws MalformedResponseException
     * @throws RuntimeException
     * @throws UndeleteableResourceException
     */
    public function delete(
        string $path,
        array $parameters = [],
        array $headers = ['Content-Type' => 'application/json']
    ) {
        $resource = ResourcePathFormatter::format($path, $parameters);
        $this->logger->debug(sprintf('Deleting data from %s (%s) on path %s', $this->apiName, $this->mode, $resource));

        return $this->request('DELETE', $resource, [
            'exceptions' => false,
            'headers' => $headers
        ]);
    }

    /**
     * @return mixed
     *
     * @throws MalformedResponseException
     * @throws GuzzleException
     * @throws AccessDeniedException
     * @throws UndeleteableResourceException
     * @throws UnreadableResourceException
     * @throws Exception
     */
    private function request(string $method, string $resource, array $options, callable $callBack = null)
    {
        $response = $this->httpClient->request($method, $resource, $options);

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logger->debug(
            sprintf('Received %d response from %s (%s)', $statusCode, $this->apiName, $this->mode),
            ['body' => $body]
        );

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if (empty($callBack)) {
            if (($statusCode < 200 || $statusCode >= 300) && $statusCode != 400) {
                throw $this->getResourceException($method, $statusCode);
            }

            if (!empty($body)) {
                return $this->parseResponse($body, $method, $resource);
            }

            return $response;
        }

        return $callBack($statusCode, $body, $method, $resource, $options['headers']);
    }

    /**
     * @return mixed
     *
     * @throws MalformedResponseException
     */
    private function parseResponse(string $body, string $method, $resource)
    {
        try {
            return JsonResponseParser::parse($body);
        } catch (InvalidJsonException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot %s resource "%s": malformed JSON returned', strtolower($method), $resource)
            );
        }
    }

    /**
     * @throws UnreadableResourceException
     * @throws UndeleteableResourceException
     */
    private function getResourceException(string $method, int $statusCode): Exception
    {
        if (strtolower($method) === 'delete') {
            throw new UndeleteableResourceException(
                sprintf('Resource could not be deleted (status code %d)', $statusCode)
            );
        }

        throw new UnreadableResourceException(sprintf('Resource could not be read (status code %d)', $statusCode));
    }
}
