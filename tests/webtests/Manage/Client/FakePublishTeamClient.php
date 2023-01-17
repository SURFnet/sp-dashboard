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
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishTeamsRepository;
use function array_key_exists;
use function json_decode;

class FakePublishTeamClient implements PublishTeamsRepository
{
    public function createTeam(array $team)
    {
        return ['id' => 'ID'];
    }

    public function changeMembership(int $id, string $role)
    {
        // TODO: Implement changeMembership() method.
    }

    public function inviteMember(array $inviteObject)
    {
        // TODO: Implement inviteMember() method.
    }

    public function resendInvitation(int $id, string $message)
    {
        // TODO: Implement resendInvitation() method.
    }
}
