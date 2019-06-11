<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryIdentityProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory\IdentityProviderFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

/**
 * The IdentityProviderClient can be used to perform queries on the Manage
 * /manage/api/internal/search/saml20_idp endpoint. Queries will return the domain objects.
 */
class IdentityProviderClient implements IdentityProviderRepository
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var Config
     */
    private $manageConfig;

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client, Config $manageConfig)
    {
        $this->client = $client;
        $this->manageConfig = $manageConfig;
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
                "state" => (string) $this->manageConfig->getPublicationStatus(),
            ]);

            $list = [];
            foreach ($result as $manageResult) {
                $list[] = IdentityProviderFactory::fromManageResult($manageResult);
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
}
