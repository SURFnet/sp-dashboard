<?php

/**
 * Copyright 2021 SURFnet B.V.
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

interface DeleteTeamsEntityRepository
{
    const RESULT_SUCCESS = 'success';

    /**
     * Delete a team from teams identified by the teams id
     */
    public function deleteTeam(int $teamId): string;

    /**
     * Delete a membership from a team identified by the membership id
     */
    public function deleteMembership(int $memberId): string;
}
