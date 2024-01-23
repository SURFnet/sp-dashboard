<?php

declare(strict_types = 1);

/**
 * Copyright 2017 SURFnet B.V.
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

use Stringable;

class Attribute implements Stringable
{
    /**
     * @var bool
     */
    private $requested;

    /**
     * @var string
     */
    private $motivation;

    /**
     * @return boolean
     */
    public function isRequested()
    {
        return $this->requested;
    }

    /**
     * @param bool $requested
     *
     * @return $this
     */
    public function setRequested($requested): static
    {
        $this->requested = $requested;

        return $this;
    }

    /**
     * @return string
     */
    public function getMotivation()
    {
        return $this->motivation;
    }

    /**
     * @param string $motivation
     *
     * @return $this
     */
    public function setMotivation($motivation): static
    {
        $this->motivation = $motivation;

        return $this;
    }

    public function hasMotivation(): bool
    {
        return trim($this->motivation) !== '' && trim($this->motivation) !== '0';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->requested) {
            return '-';
        }

        return (string)$this->motivation;
    }
}
