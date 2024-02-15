<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

interface Comparable
{
    /**
     * Serialize the entity to an array
     *
     * This, for the purpose of comparing two ManageEntities with one another.
     * Giving us an easy means to compare differences between versions of an
     * Entity. Which is usefull when writing an update of an Entity back to
     * Manage.
     *
     * Keys should match the keys found in Manage (for easy transfer back to
     * Manage later down the line).
     */
    public function asArray(): array;
}
