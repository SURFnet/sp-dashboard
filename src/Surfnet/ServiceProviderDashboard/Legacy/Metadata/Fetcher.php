<?php

//declare(strict_types = 1);

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
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Exception;

class Fetcher implements FetcherInterface
{
    private readonly int $timeout;

    private static string $curlErrorRegex = '/cURL error (\d+):/';

    /**
     * Constructor
     */
    public function __construct(
        private readonly ClientInterface $guzzle,
        private readonly LoggerInterface $logger,
        $timeout,
    ) {
        $this->timeout = (int) $timeout;
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws MetadataFetchException
     */
    public function fetch($url)
    {
        try {
            $guzzleOptions = [ 'timeout' => $this->timeout, 'verify' => false ];
            $response = $this->guzzle->request('GET', $url, $guzzleOptions);
            return $response->getBody()->getContents();
        } catch (ConnectException $e) {
            $this->logger->info('Metadata CURL exception', ['e' => $e]);
            $curlError = ' (' . $this->getCurlErrorDescription($e->getMessage()) . ').';
            throw new MetadataFetchException('Failed retrieving the metadata' . $curlError);
        } catch (Exception $e) {
            $this->logger->info('Metadata exception', ['e' => $e]);
            throw new MetadataFetchException('Failed retrieving the metadata.');
        }
    }

    /**
     * @return string
     */
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
