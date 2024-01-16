<?php

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
namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\Attribute as AttributeDto;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\AttributeFormLanguage;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\AttributeTypeInformation;
use function in_array;

class Attribute
{
    final public const ATTRIBUTE_NAME_SUFFIX = 'Attribute';

    public function __construct(private readonly string $id, private readonly string $saml20Label, private readonly string $saml20Info, private readonly string $oidcngLabel, private readonly string $oidcngInfo, private readonly string $name, private readonly array $urns, private readonly array $excludeOnEntityType)
    {
    }

    public static function fromAttribute(
        AttributeDto $attribute,
        AttributeTypeInformation $information
    ): Attribute {

        return new self(
            $attribute->id,
            $information->saml20Label,
            $information->saml20Info,
            $information->oidcngLabel,
            $information->oidcngInfo,
            $attribute->id . Attribute::ATTRIBUTE_NAME_SUFFIX,
            $attribute->urns,
            $attribute->excludeOnEntityType
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSaml20Label(): string
    {
        return $this->saml20Label;
    }

    public function getSaml20Info(): string
    {
        return $this->saml20Info;
    }

    public function getOidcngLabel(): string
    {
        return $this->oidcngLabel;
    }

    public function getOidcngInfo(): string
    {
        return $this->oidcngInfo;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrns(): array
    {
        return $this->urns;
    }

    public function isExcluded(string $protocol): bool
    {
        return in_array($protocol, $this->excludeOnEntityType);
    }
}
