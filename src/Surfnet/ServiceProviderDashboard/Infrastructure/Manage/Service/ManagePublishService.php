<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service;

use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PushMetadataException;

class ManagePublishService
{
    private array $validEnvironments = ['test', 'production'];

    public function __construct(
        private readonly PublishEntityRepository $testClient,
        private readonly PublishEntityRepository $productionClient,
    ) {
    }

    /**
     * @param  $environment
     * @throws InvalidArgumentException
     * @throws PushMetadataException
     */
    public function pushMetadata($environment): void
    {
        $this->getClient($environment)->pushMetadata();
    }

    private function getClient($environment): PublishEntityRepository
    {
        if (!in_array($environment, $this->validEnvironments)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Please set the publication mode to one of these environments (%s)',
                    implode(', ', $this->validEnvironments)
                )
            );
        }
        if ($environment === 'test') {
            return $this->testClient;
        }
        return $this->productionClient;
    }
}
