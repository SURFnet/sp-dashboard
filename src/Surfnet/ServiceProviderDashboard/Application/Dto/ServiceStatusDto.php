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

namespace Surfnet\ServiceProviderDashboard\Application\Dto;

use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;

class ServiceStatusDto implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $link;

    /**
     * @var EntityList
     */
    private $entityList;

    /**
     * @var string[]
     */
    private $states;

    /**
     * ServiceStatusDto constructor.
     * @param $name
     * @param $link
     * @param EntityList $entityList
     * @param array $states
     */
    public function __construct(
        $name,
        $link,
        EntityList $entityList,
        array $states
    ) {
        $this->name = $name;
        $this->link = $link;
        $this->entityList = $entityList;
        $this->states = $states;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'link' => $this->link,
            'entities' => $this->entityList,
            'states' => $this->states,
        ];
    }
}
