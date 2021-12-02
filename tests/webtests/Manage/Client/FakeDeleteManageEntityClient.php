<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use Surfnet\ServiceProviderDashboard\Application\Exception\UnableToDeleteEntityException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;
use function array_key_exists;

class FakeDeleteManageEntityClient implements DeleteManageEntityRepository
{
    private $deleteQueue = [];

    public function registerDeleteRequest($entityId)
    {
        $this->deleteQueue[$entityId] = true;
    }

    public function delete($manageId, $protocol)
    {

        $result = array_key_exists($manageId, $this->deleteQueue) && $this->deleteQueue[$manageId];

        if ($result !== true) {
            throw new UnableToDeleteEntityException(
                sprintf('Not allowed to delete entity with internal manage ID: "%s"', $manageId)
            );
        }
        return self::RESULT_SUCCESS;
    }
}
