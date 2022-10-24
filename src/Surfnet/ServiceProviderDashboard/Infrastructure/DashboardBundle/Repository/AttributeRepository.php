<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\AttributeInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\NullAttribute;

class AttributeRepository implements AttributeRepositoryInterface
{
    private $attributesFileLocation;
    private $attributes = [];

    public function __construct(string $attributesFileLocation)
    {
        $this->attributesFileLocation = $attributesFileLocation;
    }

    private function load(): array
    {
        return json_decode(file_get_contents($this->attributesFileLocation), true);
    }

    private function getAttributes(): array
    {
        if (empty($this->attributes)) {
            foreach ($this->load() as $rawAttribute) {
                $this->attributes[] = Attribute::fromAttribute($rawAttribute);
            }
        }
        return $this->attributes;
    }

    public function findAll(): array
    {
        return $this->getAttributes();
    }

    public function findOneByName(string $name): AttributeInterface
    {
        $attributes = $this->findAll();
        foreach ($attributes as $attribute) {
            if ($attribute->urns[0] === $name) {
                return $attribute;
            }
        }
        return new NullAttribute();
    }

    public function findAllNameSpaceIdentifiers(): array
    {
        $nameSpaceIdentifiers = [];
        foreach ($this->getAttributes() as $attribute) {
            $nameSpaceIdentifiers[] = $attribute->urns[0];
        }
        return $nameSpaceIdentifiers;
    }
}
