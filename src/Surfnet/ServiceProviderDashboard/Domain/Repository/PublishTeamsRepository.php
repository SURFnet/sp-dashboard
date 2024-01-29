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

interface PublishTeamsRepository
{
    /**
     * Create a new team in Teams.
     *
     * Expects an array with the following structure:
     *
     {
        "name": "Champions ",
        "description": "Team champions",
        "personalNote": "Team created by SP Dashboard",
        "viewable": true,
        "emails": {
            "test@test.com": "ADMIN"
        },
        "roleOfCurrentUser": "ADMIN",
        "invitationMessage": "Please..",
        "language": "DUTCH"
     }
    public function createTeam(array $team): mixed;

    /**
     * Change the membership role for a given id with a given role.
     */
    public function changeMembership(int $id, string $role): mixed;

    /**
     * Invite a new member.  Expects an array with the following structure:
     *
     {
        "teamId": 2,
        "intendedRole": "ADMIN",
        "emails": [
            "test@test.org",
            "test2@test.org"
        ],
        "message": "Please join",
        "language": "ENGLISH"
     }
     */
    public function inviteMember(array $inviteObject): mixed;

    /**
     * Resends the invite to a member given an id for the member & a message to accompany the invite.
     */
    public function resendInvitation(int $id, string $message): mixed;
}
