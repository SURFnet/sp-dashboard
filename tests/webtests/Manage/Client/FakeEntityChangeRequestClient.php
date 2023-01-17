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

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityChangeRequestRepository;

class FakeEntityChangeRequestClient implements EntityChangeRequestRepository
{
    public function openChangeRequest(ManageEntity $entity, ?ManageEntity $pristineEntity, Contact $contact): array
    {
        return ['id' => 'the-entity-id-uuid'];
    }

    public function getChangeRequest(string $id, Protocol $protocol): array
    {
        $changeRequests = [];

        switch ($id) {
            case '9729d851-cfdd-4283-a8f1-a29ba5036261':
                $changeRequests[] = json_decode(file_get_contents(
                    __DIR__ . '/../../fixtures/change-request/manage-change-request-with-note.json'
                ), true, 512);
        }
        return $changeRequests;
    }
}
