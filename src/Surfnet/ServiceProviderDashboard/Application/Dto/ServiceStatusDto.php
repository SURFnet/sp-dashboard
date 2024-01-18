<?php

//declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Application\Dto;

class ServiceStatusDto implements \JsonSerializable
{
    /**
     * ServiceStatusDto constructor.
     *
     * @param string[] $states
     * @param string[] $labels
     * @param string[] $tooltips
     * @param int      $percentage
     */
    public function __construct(private readonly array $states, private readonly array $labels, private readonly array $tooltips, private readonly array $legend, private $percentage)
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'states' => $this->states,
            'labels' => $this->labels,
            'tooltips' => $this->tooltips,
            'legend' => $this->legend,
            'percentage' => $this->percentage,
        ];
    }
}
