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
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

class PublishServiceClient implements PublishServiceRepositoryInterface
{
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
     */
    public function publish(Service $service)
    {
        $publishRequest = PublishRequest::from($service);
        $json = $this->convertToJson($publishRequest);
        return $this->client->post($json, '/api/internal/metadata');
    }

    /**
     * @param PublishRequest $request
     *
     * @return string
     */
    private function convertToJson(PublishRequest $request)
    {
        $response = $this->client->post(
            json_encode(['xml' => $request->metadataXml], true),
            '/api/client/import/xml'
        );
        return json_encode($response, true);
    }
}
