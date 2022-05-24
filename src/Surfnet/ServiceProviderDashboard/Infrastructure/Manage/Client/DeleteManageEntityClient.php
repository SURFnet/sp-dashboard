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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository as DeleteEntityRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\HttpException\HttpException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;

class DeleteManageEntityClient implements DeleteEntityRepositoryInterface
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }


    /**
     * Delete a manage entity by the internal (manage) id
     *
     * When deleting the entity succeeded, the success status is returned: 'success' in all other situations
     * an exception is thrown of type DeleteEntityFromManageException.
     *
     * @param string $manageId
     * @param string $protocol
     *
     * @return string
     * @throws UnableToDeleteEntityException
     * @throws RuntimeException
     */
    public function delete($manageId, $protocol)
    {
        try {
            $result = $this->client->delete(
                sprintf('/manage/api/internal/metadata/%s/%s', $this->getProtocol($protocol), $manageId)
            );

            if ($result !== true) {
                throw new UnableToDeleteEntityException(
                    sprintf('Not allowed to delete entity with internal manage ID: "%s"', $manageId)
                );
            }

            return self::RESULT_SUCCESS;
        } catch (HttpException $e) {
            throw new UnableToDeleteEntityException(
                sprintf('Unable to delete entity with internal manage ID: "%s"', $manageId),
                0,
                $e
            );
        }
    }

    /**
     * Creates a link between the internal protocol and the protocol defined in manage
     *
     * @throws RuntimeException
     */
    private function getProtocol(string $dashboardProtocol): string
    {
        $lookup = [
            Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER => Protocol::OAUTH20_RS,
            Constants::TYPE_OPENID_CONNECT_TNG => Protocol::OIDC10_RP,
            Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT => Protocol::OIDC10_RP,
            Constants::TYPE_SAML => Protocol::SAML20_SP,
        ];

        if (!isset($lookup[$dashboardProtocol])) {
            throw new RuntimeException(
                sprintf('The protocol "%s" can not be mapped to a manage entity type', $dashboardProtocol)
            );
        }

        return $lookup[$dashboardProtocol];
    }
}
