<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ConfiguredTestIdpCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TestIdpCollection;

class TestIdpService implements TestIdpServiceInterface
{
    public function __construct(
        private readonly ConfiguredTestIdpCollection $testIdps,
        private readonly IdentityProviderRepository $idpRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function loadTestIdps(): TestIdpCollection
    {
        $collection = new TestIdpCollection();
        foreach ($this->testIdps->testEntities() as $testIdp) {
            $idp = $this->idpRepository->findByEntityId($testIdp);
            if ($idp === null) {
                $this->logger->notice(sprintf('Unable to load test IdP: %s. EntityId not found in Manage', $testIdp));
                continue;
            }
            $collection->add($idp);
        }
        return $collection;
    }
}
