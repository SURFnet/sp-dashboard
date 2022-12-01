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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;

class AttributeNameService implements AttributeNameServiceInterface
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function getAttributeTypeNames(): array
    {
        $attributes = $this->attributeRepository->findAll();

        $attributeNames = [];
        foreach ($attributes ?? [] as $attribute) {
            $attributeNames[] = $attribute->id . AttributeNameServiceInterface::ATTRIBUTE_NAME_SUFFIX;
        }
        return $attributeNames;
    }
}