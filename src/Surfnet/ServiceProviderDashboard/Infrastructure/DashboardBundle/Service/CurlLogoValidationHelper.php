<?php

/**
 * Copyright 2018 SURFnet B.V.
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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoInvalidTypeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoNotFoundException;

class CurlLogoValidationHelper implements LogoValidationHelperInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Using Curl: tests:
     *  - is the curl response code erroneous (>= 400)
     *  - if the content type is correct
     *
     * @param $url
     * @throws LogoInvalidTypeException
     * @throws LogoNotFoundException
     */
    public function validateLogo($url)
    {
        $this->logger->debug(sprintf('Validating logo: "%s" using curl', $url));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_exec($ch);

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);


        $this->logger->debug(sprintf('Curl returned a "%d" HTTP response code', $responseCode));
        $this->logger->debug(sprintf('Downloaded a file with content type: "%s"', $contentType));

        // Test if the response is not in the 4xx / 5xx range
        if ($responseCode >= 400 || !empty($error)) {
            $this->logger->info('The logo can not be downloaded');
            if (!empty($error)) {
                $this->logger->info(sprintf('Received the following curl error: "%s"', $error));
            }
            throw new LogoNotFoundException('Downloading of the logo failed');
        }

        // Test if the resource is of the correct file type
        if ($contentType !== LogoValidationHelperInterface::IMAGE_TYPE_PNG &&
            $contentType !== LogoValidationHelperInterface::IMAGE_TYPE_GIF
        ) {
            $this->logger->info('The logo file type is invalid');
            throw new LogoInvalidTypeException('The logo file type is invalid');
        }
    }
}
