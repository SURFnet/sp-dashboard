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

use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Protocol;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;

/**
 * The QueryClient can be used to perform queries on the manage /manage/api/internal/search/saml20_sp|oidc10_rp endpoint
 *
 * Queries will return a hard coded return set per application.
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
class QueryClient implements QueryEntityRepository
{

    private $protocolSupport = [Protocol::SAML20_SP, Protocol::OIDC10_RP];

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
     * @param string $entityId
     *
     * @return string|null
     *
     * @throws QueryServiceProviderException
     */
    public function findManageIdByEntityId($entityId)
    {
        try {
            $result = $this->doSearchQuery([
                'entityid' => $entityId,
                "REQUESTED_ATTRIBUTES" => ['metaDataFields.name:en'],
            ]);

            if (isset($result[0]['_id'])) {
                return $result[0]['_id'];
            }
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entity with entityId: "%s"', $entityId),
                0,
                $e
            );
        }
    }

    /**
     * @param string $manageId
     *
     * @return string
     *
     * @throws QueryServiceProviderException
     */
    public function getMetadataXmlByManageId($manageId)
    {
        try {
            return $this->client->read(
                sprintf('/manage/api/internal/sp-metadata/%s', $manageId),
                [],
                ['Content-Type' => 'application/xml']
            );
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entity metadata with manage ID: "%s"', $manageId),
                0,
                $e
            );
        }
    }

    /**
     * @param string $manageId
     *
     * @return ManageEntity|null
     *
     * @throws QueryServiceProviderException
     */
    public function findByManageId($manageId)
    {
        try {
            // TODO: investigate if we can add the protocol to the param list of this method to prevent the try/retry
            //  construction below.
            $data = $this->client->read(
                sprintf('/manage/api/internal/metadata/saml20_sp/%s', $manageId)
            );
            // If the saml endpoint yields no results, try the oidc.
            if (empty($data)) {
                $data = $this->client->read(
                    sprintf('/manage/api/internal/metadata/oidc10_rp/%s', $manageId)
                );
            }
            if (empty($data)) {
                return null;
            }
            return ManageEntity::fromApiResponse($data);
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entity with internal manage ID: "%s"', $manageId),
                0,
                $e
            );
        }
    }

    /**
     * Query manage for all test entities by given team name.
     *
     * @param string $teamName
     * @param string $state
     *
     * @return ManageEntity[]|null
     *
     * @throws QueryServiceProviderException
     */
    public function findByTeamName($teamName, $state)
    {
        try {
            // Query manage to get the internal id of every SP entity with given team ID.
            $searchResults = $this->doSearchQuery([
                'metaDataFields.coin:service_team_id' => $teamName,
                'state' => $state
            ]);

            // For each search result, query manage to get the full SP entity data.
            return array_map(
                function ($result) {
                    return $this->findByManageId($result['_id']);
                },
                $searchResults
            );
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entities with team ID: "%s"', $teamName),
                0,
                $e
            );
        }
    }

    /**
     * @param $teamName
     * @param $state
     * @return array
     * @throws QueryServiceProviderException
     */
    public function findOidcngResourceServersByTeamName($teamName, $state)
    {
        try {
            // Query manage to get the internal id of every SP entity with given team ID.
            $params = [
                'metaDataFields.coin:service_team_id' => $teamName,
                'state' => $state
            ];

            $searchResults = $this->client->post(
                json_encode($params),
                sprintf('/manage/api/internal/search/oidc10_rp')
            );

            // For each search result, query manage to get the full SP entity data.
            return array_filter(array_map(
                function ($result) {
                    $entity = $this->findByManageId($result['_id']);
                    if ($entity && $entity->isOidcngResourceServer()) {
                        return $entity;
                    }
                },
                $searchResults
            ));
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find oidcng resource server entities with team ID: "%s"', $teamName),
                0,
                $e
            );
        }
    }

    /**
     * Search for both oidc and saml entities.
     *
     * @param array $params
     * @return array|null
     * @throws HttpException
     */
    private function doSearchQuery(array $params)
    {
        $results = [];
        foreach ($this->protocolSupport as $protocol) {
            $response = $this->client->post(
                json_encode($params),
                sprintf('/manage/api/internal/search/%s', $protocol)
            );
            $results += $response;
        }
        return $results;
    }
}
