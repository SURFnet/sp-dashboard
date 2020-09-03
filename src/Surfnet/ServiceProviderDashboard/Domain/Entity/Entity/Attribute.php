<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use Webmozart\Assert\Assert;

class Attribute
{
    private $name;
    private $value;
    private $source;
    private $motivation;

    public static function fromApiResponse($attributeName, array $attributeData)
    {
        $value = $attributeData['value'];
        $source = isset($attributeData['source']) ? $attributeData['source'] : '';
        $motivation = isset($attributeData['motivation']) ? $attributeData['motivation'] : '';

        Assert::stringNotEmpty($attributeName, 'The attribute name must be non-empty string');
        Assert::stringNotEmpty($value, 'The attribute value must be non-empty string');
        Assert::string($source, 'The attribute source must be string');
        Assert::string($motivation, 'The attribute motivation must be string');

        return new self(
            $attributeName,
            $value,
            $source,
            $motivation
        );
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $source
     * @param string $motivation
     */
    public function __construct($name, $value, $source, $motivation)
    {
        $this->name = $name;
        $this->value = $value;
        $this->source = $source;
        $this->motivation = $motivation;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function hasMotivation()
    {
        return $this->motivation !== null && $this->motivation !== '';
    }

    public function getMotivation()
    {
        return $this->motivation;
    }
}
