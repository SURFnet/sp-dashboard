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

use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryIdentityProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

/**
 * The QueryClient can be used to perform queries on the manage /manage/api/internal/search/saml20_sp endpoint. Queries
 * will return a hard coded return set per application.
 *
 * Example response (json formatted for readability)
 *  [{
 *      "_id": "db2e5c63-3c54-4962-bf4a-d6ced1e9cf33",
 *      "version": 0,
 *      "data": {
 *          "entityid": "https://example.com/saml/metadata",
 *          "state": "prodaccepted",
 *          "metaDataFields": {
 *              "name:en": "My example SP"
 *          }
 *      }
 *  }]
 */
class IdentityProviderClient implements IdentityProviderRepository
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
     * @return IdentityProvider[]
     *
     * @throws QueryIdentityProviderException
     */
    public function findAll()
    {
        try {
            $result = $this->doSearchQuery([
                "state" => "prodaccepted",
            ]);

            $list = [];
            foreach ($result as $manageResult) {
                $list[] = $this->parseManageResult($manageResult);
            }
            return $list;
        } catch (HttpException $e) {
            throw new QueryIdentityProviderException(
                sprintf('Unable to find identity providers: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * @param array $params
     * @return array|null
     * @throws HttpException
     */
    private function doSearchQuery(array $params)
    {
        return $this->client->post(
            json_encode($params),
            '/manage/api/internal/search/saml20_idp'
        );
    }

    /**
     * @param $manageResult
     * @return IdentityProvider
     */
    private function parseManageResult($manageResult)
    {
        return new IdentityProvider(
            $manageResult['_id'],
            $manageResult['data']['entityid'],
            $manageResult['data']['metaDataFields']['name:nl'],
            $manageResult['data']['metaDataFields']['name:en']);
    }
}
