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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository as PublishEntityRepositoryInterface;

class FakePublishEntityClient implements PublishEntityRepositoryInterface
{
    private array $publishResponses = [];
    private bool $pushOk = true;
    private string $path = __DIR__ . '/../../../../var/webtest-manage.json';

    public function reset()
    {
        $this->write([]);
    }

    public function registerPublishResponse(string $entityId, string $response)
    {
        $data = $this->read();
        $data[$entityId] = $response;
        $this->write($data);
    }

    public function registerPushFail()
    {
        $this->pushOk = false;
    }

    public function publish(
        ManageEntity $entity,
        ?ManageEntity $pristineEntity,
        Contact $contact,
        string $part = ''
    ): mixed {
        $entityId = $entity->getMetaData()->getEntityId();
        $publishResponses = $this->read();
        if (!array_key_exists($entityId, $publishResponses)) {
            throw new RuntimeException(sprintf('No pre programmed response is available for entity "%s"', $entityId));
        }
        return json_decode($publishResponses[$entityId], true);
    }

    public function pushMetadata(): mixed
    {
        $response = $this->pushOk ? '{"status":"OK"}' : '{"status":"400"}';
        return json_decode($response, true);
    }

    private function read()
    {
        return json_decode(file_get_contents($this->path), true);
    }

    private function write(array $data): void
    {
        file_put_contents($this->path, json_encode($data));
    }
}
