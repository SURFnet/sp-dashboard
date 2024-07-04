<?php

declare(strict_types = 1);

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
    public static function fromApiResponse($attributeName, array $attributeData): Attribute
    {
        $value = $attributeData['value'];
        $source = $attributeData['source'] ?? '';
        $motivation = $attributeData['motivation'] ?? '';
        $releaseAs = $attributeData['release_as'] ?? '';
        $useAsNameId = $attributeData['use_as_name_id'] ?? false;

        Assert::stringNotEmpty($attributeName, 'The attribute name must be non-empty string');
        Assert::stringNotEmpty($value, 'The attribute value must be non-empty string');
        Assert::string($source, 'The attribute source must be string');
        Assert::string($releaseAs, 'The attribute release_as must be string');
        Assert::boolean($useAsNameId, 'The attribute use_as_name_id must be boolean');
        Assert::string($motivation, 'The attribute motivation must be string');

        return new self(
            $attributeName,
            $value,
            $source,
            $motivation,
            $releaseAs,
            $useAsNameId,
        );
    }

    public function __construct(
        private readonly string $name,
        private readonly string $value,
        private string $source,
        private ?string $motivation,
        private string $releaseAs,
        private bool $useAsNameId,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function hasMotivation(): bool
    {
        return $this->motivation !== null && $this->motivation !== '';
    }

    public function getMotivation(): string
    {
        return $this->motivation;
    }

    public function getReleaseAs(): string
    {
        return $this->releaseAs;
    }

    public function getUseAsNameId(): bool
    {
        return $this->useAsNameId;
    }

    public function updateSource(string $newSource): static
    {
        $this->source = $newSource;
        return clone $this;
    }

    public function updateMotivation(string $motivation): self
    {
        $this->motivation = $motivation;
        return clone $this;
    }
}
