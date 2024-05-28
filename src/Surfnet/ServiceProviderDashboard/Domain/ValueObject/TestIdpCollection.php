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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

use ArrayIterator;
use IteratorAggregate;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Traversable;

class TestIdpCollection implements IteratorAggregate
{
    private array $idps;

    public function add(IdentityProvider $identityProvider): void
    {
        $this->idps[] = $identityProvider;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->idps);
    }
}
