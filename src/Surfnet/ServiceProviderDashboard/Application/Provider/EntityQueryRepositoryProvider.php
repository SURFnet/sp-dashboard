<?php

//declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Application\Provider;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;

class EntityQueryRepositoryProvider
{
    public function __construct(
        private readonly QueryManageRepository $manageTestQueryClient,
        private readonly QueryManageRepository $manageProductionQueryClient
    ) {
    }

    public function fromEnvironment(string $environment): QueryManageRepository
    {
        return match ($environment) {
            Constants::ENVIRONMENT_TEST => $this->manageTestQueryClient,
            Constants::ENVIRONMENT_PRODUCTION => $this->manageProductionQueryClient,
            default => throw new InvalidArgumentException(sprintf('Unsupported environment "%s" requested.', $environment)),
        };
    }

    public function getManageTestQueryClient(): QueryManageRepository
    {
        return $this->manageTestQueryClient;
    }

    public function getManageProductionQueryClient(): QueryManageRepository
    {
        return $this->manageProductionQueryClient;
    }
}
