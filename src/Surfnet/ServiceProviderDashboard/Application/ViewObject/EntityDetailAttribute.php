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

class EntityDetailAttribute
{
    public $label;

    public $value;

    public $informationPopup;

    /**
     * Marks if the attribute is excluded for one of the entity protocol
     * types For example. The EduPersonTargettedId is not displayed on the
     * Oidcng entities.
     *
     * @var string[]
     */
    public $excludedFor = [];

    public function isExcludedForProtocol(string $protocol): bool
    {
        return in_array($protocol, $this->excludedFor);
    }
}
