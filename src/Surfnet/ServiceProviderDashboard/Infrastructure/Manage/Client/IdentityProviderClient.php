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

use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig as Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\EntityId;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryIdentityProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory\IdentityProviderFactory;

/**
 * The IdentityProviderClient can be used to perform queries on the Manage
 * /manage/api/internal/search/saml20_idp endpoint. Queries will return the domain objects.
 */
class IdentityProviderClient implements IdentityProviderRepository
{
    public function __construct(
        private readonly HttpClient $client,
        private readonly Config $manageConfig,
    ) {
    }

    /**
     * @return IdentityProvider[]
     *
     * @throws QueryIdentityProviderException
     */
    public function findAll()
    {
        try {
            // Based on the manage config set (prod or test) we retrieve the correct results from the manage idp client.
            $result = $this->doSearchQuery(
                [
                "state" => $this->manageConfig->getPublicationStatus()->getStatus(),
                ]
            );

            $list = [];

            if ($result === null) {
                return $list;
            }
            foreach ($result as $manageResult) {
                $idp = IdentityProviderFactory::fromManageResult($manageResult);
                $list[$idp->getEntityId()] = $idp;
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

    public function findByInstitutionId(InstitutionId $institutionId): array
    {
        try {
            $result = $this->doSearchQuery(
                [
                    'metaDataFields.coin:institution_id' => (string) $institutionId,
                ]
            );

            $list = [];
            if ($result === null) {
                return $list;
            }
            foreach ($result as $manageResult) {
                $result = IdentityProviderFactory::fromManageResult($manageResult);
                $list[$result->getEntityId()] = $result;
            }
            return $list;
        } catch (HttpException $e) {
            throw new QueryIdentityProviderException(
                sprintf('Unable to find identity providers by institution id: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * @throws HttpException
     */
    private function doSearchQuery(array $params): ?array
    {
        return $this->client->post(
            json_encode($params),
            '/manage/api/internal/search/saml20_idp'
        );
    }
}
