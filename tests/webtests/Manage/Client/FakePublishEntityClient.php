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

use RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository as PublishEntityRepositoryInterface;
use function array_key_exists;
use function json_decode;

class FakePublishEntityClient implements PublishEntityRepositoryInterface
{
    private $publishResponses;
    private $pushOk = true;

    public function registerPublishResponse(string $entityId, string $response)
    {
        $this->publishResponses[$entityId] = $response;
    }

    public function registerPushFail()
    {
        $this->pushOk = false;
    }

    public function publish(ManageEntity $entity, string $part = '')
    {
        $entityId = $entity->getMetaData()->getEntityId();
        if (!array_key_exists($entityId, $this->publishResponses)) {
            throw new RuntimeException(sprintf('No pre programmed response is available for entity "%s"', $entityId));
        }
        return json_decode($this->publishResponses[$entityId], true);
    }

    public function pushMetadata()
    {
        $response = $this->pushOk ? '{"status":"OK"}' : '{"status":"400"}';
        return json_decode($response, true);
    }
}
