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
    private $label;

    /**
     * @var string
     */
    private $info;

    /**
     * @var string
     */
    private $name;

    private $urns = [];

    public function __construct(
        string $id,
        string $label,
        string $info,
        string $name,
        array $urns
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->info = $info;
        $this->name = $name;
        $this->urns = $urns;
    }

    public static function fromAttribute(
        AttributeDto $attribute,
        AttributeTypeInformation $information
    ): Attribute {

        return new self(
            $attribute->id,
            $information->label,
            $information->info,
            $attribute->id . ATTRIBUTE::ATTRIBUTE_NAME_SUFFIX,
            $attribute->urns
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getInfo(): string
    {
        return $this->info;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrns(): array
    {
        return $this->urns;
    }
}
