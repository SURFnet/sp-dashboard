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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\UnexpectedResultException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;
use function in_array;
use function sprintf;

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
class QueryClient implements QueryManageRepository
{

    private array $protocolSupport = [Protocol::SAML20_SP, Protocol::OIDC10_RP, Protocol::OAUTH20_RS];

    public function __construct(private readonly HttpClientInterface $client)
    {
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
            $result = $this->doSearchQuery(
                [
                'entityid' => $entityId,
                "REQUESTED_ATTRIBUTES" => ['metaDataFields.name:en'],
                ]
            );

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

        return null;
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
            // phpcs:ignore
            // TODO: investigate if we can add the protocol to the param list of this method to prevent the try/retry
            //  construction below.
            $data = $this->read(Protocol::SAML20_SP, $manageId);
            // If the saml endpoint yields no results, try the oidc.
            if ($data === null || $data === []) {
                $data = $this->read(Protocol::OIDC10_RP, $manageId);
            }
            if ($data === null || $data === []) {
                $data = $this->read(Protocol::OAUTH20_RS, $manageId);
            }
            if ($data === null || $data === []) {
                return null;
            }

            $this->loadDetailedResourceServers($data);

            return ManageEntity::fromApiResponse($data);
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entity with internal manage ID: "%s"', $manageId),
                0,
                $e
            );
        }
    }

    public function findByManageIdAndProtocol(string $manageId, string $protocol) :? ManageEntity
    {
        try {
            $data = $this->read($protocol, $manageId);
            if ($data === null || $data === []) {
                return null;
            }
            $this->loadDetailedResourceServers($data);
            return ManageEntity::fromApiResponse($data);
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entity with internal manage ID: "%s" and protocol "%s"', $manageId, $protocol),
                0,
                $e
            );
        }
    }

    private function read(string $protocol, string $manageId) :? array
    {
        if (!in_array($protocol, $this->protocolSupport)) {
            throw new InvalidArgumentException(sprintf('You are to read an unsupported protocol "%s"', $protocol));
        }

        return $this->client->read(
            sprintf('/manage/api/internal/metadata/%s/%s', $protocol, $manageId)
        );
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
            $searchResults = $this->doSearchQuery(
                [
                'metaDataFields.coin:service_team_id' => $teamName,
                'state' => $state,
                ]
            );

            // For each search result, query manage to get the full SP entity data.
            return array_map(
                fn($result): ?ManageEntity => $this->findByManageIdAndProtocol($result['_id'], $result['type']),
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
     * @return ManageEntity[]|null
     * @throws QueryServiceProviderException
     */
    public function findByInstitutionId(InstitutionId $institutionId, string $state): ?array
    {
        try {
            // Query manage to get the internal id of every SP entity with given team ID.
            $searchResults = $this->doSearchQuery(
                [
                    'metaDataFields.coin:institution_id' => (string) $institutionId,
                    'state' => $state,
                ]
            );

            // For each search result, query manage to get the full SP entity data.
            return array_map(
                fn($result): ?ManageEntity => $this->findByManageIdAndProtocol($result['_id'], $result['type']),
                $searchResults
            );
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find entities with coin:institution_id: "%s"', $institutionId),
                0,
                $e
            );
        }
    }

    public function findOidcngResourceServersByTeamName(string $teamName, string $state): array
    {
        try {
            // Query manage to get the internal id of every SP entity with given team ID.
            $params = [
                'metaDataFields.coin:service_team_id' => $teamName,
                'state' => $state,
            ];

            $searchResults = $this->client->post(
                json_encode($params),
                '/manage/api/internal/search/oauth20_rs'
            );

            // For each search result, query manage to get the full SP entity data.
            return array_filter(
                array_map(
                    function (array $result) {
                        $entity = $this->findByManageId($result['_id']);
                        if ($entity !== null) {
                            return $entity;
                        }
                    },
                    $searchResults
                )
            );
        } catch (HttpException $e) {
            throw new QueryServiceProviderException(
                sprintf('Unable to find oidcng resource server entities with team ID: "%s"', $teamName),
                0,
                $e
            );
        }
    }

    public function findResourceServerByEntityId($entityId, $state)
    {
        $params = [
            'entityid' => $entityId,
            'state' => $state,
        ];

        $searchResults = $this->client->post(
            json_encode($params),
            '/manage/api/internal/search/oauth20_rs'
        );

        $count = count($searchResults);
        if ($count != 1) {
            throw new UnexpectedResultException(
                sprintf(
                    'Unable to find resource server with entityId "%s". Expected one search result, found %s results. ' .
                    'Please verify this entity exists in Manage. ',
                    $entityId,
                    $count
                )
            );
        }

        $searchResult = reset($searchResults);
        return $this->findByManageId($searchResult['_id']);
    }

    /**
     * Search for both oidc and saml entities.
     *
     * @throws HttpException
     */
    private function doSearchQuery(array $params): array
    {
        $results = [];
        foreach ($this->protocolSupport as $protocol) {
            $response = $this->client->post(
                json_encode($params),
                sprintf('/manage/api/internal/search/%s', $protocol)
            );
            $results = array_merge($response, $results);
        }
        return $results;
    }

    private function loadDetailedResourceServers(array &$data): void
    {
        // When loading OIDCng entities, we also want details of the connected resource servers
        $manageProtocol = $data['type'] ?? '';
        $isResourceServer = (isset($data['data']['metaDataFields']['isResourceServer']) &&
            $data['data']['metaDataFields']['isResourceServer']);

        if ($manageProtocol === Protocol::OIDC10_RP && !$isResourceServer) {
            $resourceServers = [];
            $rs = $data['data']['allowedResourceServers'] ?? [];
            foreach ($rs as $resourceServer) {
                $resourceServers[] = $this->findResourceServerByEntityId(
                    $resourceServer['name'],
                    $data['data']['state']
                );
            }
            $data['resourceServers'] = $resourceServers;
        }
    }
}
