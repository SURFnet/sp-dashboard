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
    const ATTRIBUTE_NAME_SUFFIX = 'Attribute';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $saml20Label;

    /**
     * @var string
     */
    private $saml20Info;

    /**
     * @var string
     */
    private $oidcngLabel;

    /**
     * @var string
     */
    private $oidcngInfo;

    /**
     * @var string
     */
    private $name;

    private $urns = [];

    private $excludeOnEntityType = [];

    public function __construct(
        string $id,
        string $saml20Label,
        string $saml20Info,
        string $oidcngLabel,
        string $oidcngInfo,
        string $name,
        array $urns,
        array $excludeOnEntityType
    ) {
        $this->id = $id;
        $this->saml20Label = $saml20Label;
        $this->saml20Info = $saml20Info;
        $this->oidcngLabel = $oidcngLabel;
        $this->oidcngInfo = $oidcngInfo;
        $this->name = $name;
        $this->urns = $urns;
        $this->excludeOnEntityType = $excludeOnEntityType;
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
