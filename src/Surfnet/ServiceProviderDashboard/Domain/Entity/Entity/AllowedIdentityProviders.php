<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Webmozart\Assert\Assert;
use function in_array;

class AllowedIdentityProviders
{
    public function __construct(
        private array $providers,
        private bool $allowAll,
    ) {
        Assert::allString($providers);
    }

    public static function fromApiResponse(array $data): AllowedIdentityProviders
    {
        $providers = [];
        $allowAll = true;
        if (isset($data['data']['allowedall']) && $data['data']['allowedall'] !== true) {
            $allowAll = false;

            $entities = $data['data']['allowedEntities'];
            foreach ($entities as $entity) {
                $providers[] = $entity['name'];
            }
        }

        return new self($providers, $allowAll);
    }

    public function getAllowedIdentityProviders(): array
    {
        return $this->providers;
    }

    public function isAllowAll(): bool
    {
        return $this->allowAll;
    }

    public function isWhitelisted(IdentityProvider $provider): bool
    {
        return in_array($provider->getEntityId(), $this->providers);
    }

    public function merge(?AllowedIdentityProviders $allowedIdPs): void
    {
        if (!$allowedIdPs instanceof AllowedIdentityProviders) {
            $this->providers = [];
            $this->allowAll = true;
            return;
        }
        $this->providers = is_null($allowedIdPs->getAllowedIdentityProviders()) ?
            null : $allowedIdPs->getAllowedIdentityProviders();
        $this->allowAll = is_null($allowedIdPs->isAllowAll()) ? null : $allowedIdPs->isAllowAll();
    }

    public function clear(): void
    {
        $this->allowAll = true;
        $this->providers = [];
    }
}
