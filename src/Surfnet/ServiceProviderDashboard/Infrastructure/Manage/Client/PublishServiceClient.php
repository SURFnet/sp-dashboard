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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishServiceRepository as PublishServiceRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\ConvertMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class PublishServiceClient implements PublishServiceRepositoryInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Service $service
     *
     * @return mixed
     *
     * @throws PublishMetadataException
     */
    public function publish(Service $service)
    {

        $json = $this->retrieveJsonMetadata($service->getMetadataXml());

        try {
            $response = $this->client->post(
                $json,
                '/manage/api/internal/metadata',
                [],
                ['Content-Type' => 'application/json']
            );
            return $response;
        } catch (HttpException $e) {
            throw new PublishMetadataException('Unable to publish the metadata to Manage', 0, $e);
        }
    }

    /**
     * @return mixed
     *
     * @throws PushMetadataException
     */
    public function pushMetadata()
    {
        try {
            $response = $this->client->read(
                '/manage/api/internal/push',
                [],
                ['Content-Type' => 'application/json']
            );
        } catch (HttpException $e) {
            throw new PushMetadataException('Unable to push the metadata to Engineblock', 0, $e);
        }

        if ($response['status'] != "OK") {
            throw new PushMetadataException('Pushing did not succeed.');
        }
        return $response;
    }

    /**
     * Converts the Metadata XML to the Manage JSON format.
     *
     * @param $xml
     *
     * @return string
     *
     * @throws ConvertMetadataException
     */
    private function retrieveJsonMetadata($xml)
    {
        $json = json_encode(['xml', $xml]);
        try {
            $response = $this->client->post(
                $json,
                '/manage/api/internal/convert',
                [],
                ['Content-Type' => 'application/json']
            );
            return json_encode($response);
        } catch (HttpException $e) {
            throw new ConvertMetadataException('Unable to convert the XML metadata to JSON', 0, $e);
        }
    }
}
