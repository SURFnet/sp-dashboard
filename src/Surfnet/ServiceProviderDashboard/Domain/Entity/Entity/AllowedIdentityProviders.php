<?php

/**
 * Copyright 2019 SURFnet B.V.
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

class AllowedIdentityProviders
{
    /**
     * @var string[]
     */
    private $providers = [];

    /**
     * @var bool
     */
    private $allowAll;

    public static function fromApiResponse(array $data)
    {
        $instance = new self();

        $instance->allowAll = true;
        if (isset($data['data']['allowedall']) && $data['data']['allowedall'] !== true) {
            $instance->allowAll = false;

            $entities = $data['data']['allowedEntities'];
            foreach ($entities as $entity) {
                $instance->providers[] = $entity['name'];
            }
        }

        return $instance;
    }

    /**
     * @return string[]
     */
    public function getAllowedIdentityProviders()
    {
        return $this->providers;
    }

    /**
     * @return bool
     */
    public function isAllowAll()
    {
        return $this->allowAll;
    }
}
