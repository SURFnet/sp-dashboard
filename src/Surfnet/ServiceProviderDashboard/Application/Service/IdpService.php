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

use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ConfiguredTestIdpCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\IdpCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionIdpCollection;

class IdpService implements IdpServiceInterface
{
    public function __construct(
        private readonly ConfiguredTestIdpCollection $testIdps,
        private readonly EntityAclService $entityAclService,
        private readonly IdentityProviderRepository $identityProviderRepository,
    ) {
    }

    public function createCollection(): IdpCollection
    {
        $testEntities = $this->testIdps->testEntities();
        $allEntities = $this->entityAclService->getAvailableIdps();

        return new IdpCollection($testEntities, $allEntities);
    }

    public function findInstitutionIdps(InstitutionId $institutionId): InstitutionIdpCollection
    {
        return new InstitutionIdpCollection($this->identityProviderRepository->findByInstitutionId($institutionId));
    }
}
