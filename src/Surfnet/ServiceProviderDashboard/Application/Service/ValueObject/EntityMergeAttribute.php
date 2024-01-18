<?php

declare(strict_types = 1);

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
namespace Surfnet\ServiceProviderDashboard\Application\Service\ValueObject;

class EntityMergeAttribute
{
    public function __construct(
        private readonly string $name,
        private readonly string $urn
    ) {
    }

    public static function fromAttribute(
        $name,
        $urn
    ): EntityMergeAttribute {
        return new self(
            $name,
            $urn
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrn(): string
    {
        return $this->urn;
    }
}
