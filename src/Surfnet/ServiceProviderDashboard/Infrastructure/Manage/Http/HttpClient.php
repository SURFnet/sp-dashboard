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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidJsonException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\AccessDeniedException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\MalformedResponseException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\UndeleteableResourceException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\UnreadableResourceException;

final class HttpClient
{
    const MODE_TEST = 'test';
    const MODE_PROD = 'production';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param ClientInterface $httpClient
     * @param LoggerInterface $logger
     * @param string $mode The mode is used mainly for logging purposes, stating which environment was targeted.
     */
    public function __construct(ClientInterface $httpClient, LoggerInterface $logger, $mode = self::MODE_TEST)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->mode = $mode;
    }

    /**
     * @param string $path A URL path, optionally containing printf parameters. The parameters
     *               will be URL encoded and formatted into the path string.
     *               Example: "connections/%d.json"
     * @param array $parameters
     * @param array $headers
     *
     * @return mixed $data
     *
     * @throws AccessDeniedException
     * @throws UnreadableResourceException
     * @throws MalformedResponseException
     */
    public function read($path, array $parameters = [], array $headers = ['Content-Type' => 'application/json'])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);

        $this->logger->debug(
            sprintf('Getting resource %s from manage (%s)', $resource, $this->mode)
        );

        $response = $this->httpClient->request('GET', $resource, [
            'exceptions' => false,
            'headers' => $headers
        ]);
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logger->debug(
            sprintf('Received %d response from manage (%s)', $statusCode, $this->mode),
            ['body' => $body]
        );

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new UnreadableResourceException(sprintf('Resource could not be read (status code %d)', $statusCode));
        }

        if ((isset($headers['Content-Type'])) &&
            ($headers['Content-Type'] === 'application/json')) {
            try {
                $body = JsonResponseParser::parse($body);
            } catch (InvalidJsonException $e) {
                throw new MalformedResponseException(
                    sprintf('Cannot read resource "%s": malformed JSON returned', $resource)
                );
            }
        }

        return $body;
    }

    /**
     * @param mixed $data
     * @param string $path
     * @param array $parameters
     * @param array $headers
     *
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws MalformedResponseException
     * @throws UnreadableResourceException
     */
    public function post($data, $path, $parameters = [], array $headers = ['Content-Type' => 'application/json'])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);

        $this->logger->debug(
            sprintf('Posting data to manage (%s) on path %s', $this->mode, $resource),
            ['data' => $data]
        );

        $response = $this->httpClient->request('POST', $resource, [
            'exceptions' => false,
            'body' => $data,
            'headers' => $headers
        ]);
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logger->debug(
            sprintf('Received %d response from manage (%s)', $statusCode, $this->mode),
            ['body' => $body]
        );

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if (($statusCode < 200 || $statusCode >= 300) && $statusCode != 400) {
            throw new UnreadableResourceException(sprintf('Resource could not be read (status code %d)', $statusCode));
        }

        try {
            $data = JsonResponseParser::parse($body);
        } catch (InvalidJsonException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot read resource "%s": malformed JSON returned', $resource)
            );
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @param string $path
     * @param array $parameters
     * @param array $headers
     *
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws MalformedResponseException
     * @throws UnreadableResourceException
     */
    public function put($data, $path, $parameters = [], array $headers = ['Content-Type' => 'application/json'])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);

        $this->logger->debug(
            sprintf('Putting data to manage (%s) on path %s', $this->mode, $resource),
            ['data' => $data]
        );

        $response = $this->httpClient->request('PUT', $resource, [
            'exceptions' => false,
            'body' => $data,
            'headers' => $headers
        ]);
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logger->debug(
            sprintf('Received %d response from manage (%s)', $statusCode, $this->mode),
            ['body' => $body]
        );

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if (($statusCode < 200 || $statusCode >= 300) && $statusCode != 400) {
            throw new UnreadableResourceException(sprintf('Resource could not be read (status code %d)', $statusCode));
        }

        try {
            $data = JsonResponseParser::parse((string) $response->getBody());
        } catch (InvalidJsonException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot read resource "%s": malformed JSON returned', $resource)
            );
        }

        return $data;
    }

    /**
     * @param string $path
     * @param array $parameters
     * @param array $headers
     *
     * @return mixed
     *
     * @throws AccessDeniedException
     * @throws MalformedResponseException
     * @throws UnreadableResourceException
     */
    public function delete($path, $parameters = [], array $headers = ['Content-Type' => 'application/json'])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);

        $this->logger->debug(sprintf('Deleting data from manage (%s) on path %s', $this->mode, $resource));

        $response = $this->httpClient->request('DELETE', $resource, [
            'exceptions' => false,
            'headers' => $headers
        ]);

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        $this->logger->debug(
            sprintf('Received %d response from manage (%s)', $statusCode, $this->mode),
            ['body' => $body]
        );

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if (($statusCode < 200 || $statusCode >= 300) && $statusCode != 400) {
            throw new UndeleteableResourceException(
                sprintf('Resource could not be deleted (status code %d)', $statusCode)
            );
        }

        try {
            $data = JsonResponseParser::parse((string) $response->getBody());
        } catch (InvalidJsonException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot delete resource "%s": malformed JSON returned', $resource)
            );
        }

        return $data;
    }
}
