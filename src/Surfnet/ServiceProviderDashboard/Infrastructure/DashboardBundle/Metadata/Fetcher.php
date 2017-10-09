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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Metadata;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;

class Fetcher implements FetcherInterface
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $timeout;

    private static $curlErrorRegex = '/cURL error (\d+):/';

    /**
     * Constructor
     *
     * @param ClientInterface $guzzle
     * @param LoggerInterface $logger
     */
    public function __construct(ClientInterface $guzzle, LoggerInterface $logger, $timeout)
    {
        $this->guzzle = $guzzle;
        $this->logger = $logger;
        $this->timeout = (int) $timeout;
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function fetch($url)
    {
        try {
            $guzzleOptions = [ 'timeout' => $this->timeout, 'verify' => false ];
            $response = $this->guzzle->request('GET', $url, $guzzleOptions);
            $responseXml = $response->getBody()->getContents();
            return $responseXml;
        } catch (ConnectException $ex) {
            $this->logger->info('Metadata CURL exception', array('e' => $ex));
            $curlError = ' (' . $this->getCurlErrorDescription($ex->getMessage()) . ').';
            throw new InvalidArgumentException('Failed retrieving the metadata' . $curlError);
        } catch (\Exception $e) {
            $this->logger->info('Metadata exception', array('e' => $e));
            throw new InvalidArgumentException('Failed retrieving the metadata.');
        }
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function getCurlErrorDescription($message)
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

        if (!empty($error)) {
            $error .= ' - ';
        }

        return $error . ' message ' . $message;
    }

    private function extractErrorNumber($message)
    {
        $matches = [];
        preg_match(self::$curlErrorRegex, $message, $matches);
        if (is_numeric($matches[0])) {
            return $matches[0];
        }
        return $message;
    }
}
