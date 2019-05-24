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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;

class EntityAclService
{
    /**
     * @var IdentityProviderRepository
     */
    private $identityProviderRepository;

    /**
     * @var IdentityProvider[]|null
     */
    public $availableProviders = null;

    public function __construct(
        IdentityProviderRepository $identityProviderRepository
    ) {
        $this->identityProviderRepository = $identityProviderRepository;
    }

    /**
     * @param Entity $entity
     * @return IdentityProvider[]
     */
    public function getAllowedIdpsFromEntity(Entity $entity)
    {
        $availableIdps = $this->getAvailableIdps();

        $allowedIdps = [];
        if (!$entity->isIdpAllowAll()) {
            foreach ($availableIdps as $idp) {
                if ($entity->isWhitelisted($idp)) {
                    $allowedIdps[] = $idp;
                }
            }
        }

        return $allowedIdps;
    }

    /**
     * @return IdentityProvider[]
     */
    public function getAvailableIdps()
    {
        if ($this->availableProviders === null) {
            $providers = $this->identityProviderRepository->findAll();
            usort($providers, [EntityAclService::class, 'sortOnName']);
            $this->availableProviders = $providers;
        }

        return $this->availableProviders;
    }

    /**
     * Sort idp's on name
     *
     * @param IdentityProvider $a
     * @param IdentityProvider $b
     * @return int
     */
    public static function sortOnName(IdentityProvider $a, IdentityProvider $b)
    {
        if ($a->getName() == $b->getName()) {
            return 0;
        }
        return $a->getName() > $b->getName() ? 1 : -1;
    }
}
