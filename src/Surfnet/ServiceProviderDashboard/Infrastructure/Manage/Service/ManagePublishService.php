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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PushMetadataException;

class ManagePublishService
{
    private $validEnvironments = ['test', 'production'];

    /**
     * @var PublishEntityClient
     */
    private $testClient;

    /**
     * @var PublishEntityClient
     */
    private $productionClient;

    public function __construct(PublishEntityClient $test, PublishEntityClient $production)
    {
        $this->testClient = $test;
        $this->productionClient = $production;
    }

    /**
     * @param string $environment
     * @param Entity $entity
     * @throws InvalidArgumentException
     * @throws PublishMetadataException
     */
    public function publish($environment, Entity $entity)
    {
        $this->getClient($environment)->publish($entity);
    }

    /**
     * @param string $environment
     * @throws InvalidArgumentException
     * @throws PushMetadataException
     */
    public function pushMetadata($environment)
    {
        $this->getClient($environment)->pushMetadata();
    }

    private function getClient($environment)
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
