<?php

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

namespace Surfnet\ServiceProviderDashboard\Domain\Repository;

interface PublishEntityRepository
{
    /**
     * Publishes the Entity to a Service registry (like Manage, ..) This action might also result in the
     * sending of a mail message to a service desk who in turn can publish the entity in the registry.
     *
     * @param ManageEntity $entity
     * @return mixed
     */
    public function publish(ManageEntity $entity);

    /**
     * Push the metadata from Manage to Engineblock
     *
     * @return mixed
     */
    public function pushMetadata();
}
