<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Security\Voter;

use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ManageEntityAccessGrantedVoter extends Voter
{
    const MANAGE_ENTITY_ACCESS = "MANAGE_ENTITY_ACCESS";

    /**
     * @var EntityServiceInterface
     */
    private $entityService;

    public function __construct(EntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
    }

    protected function supports($attribute, $subject)
    {
        $supportedAttribute = $attribute === self::MANAGE_ENTITY_ACCESS;
        $subjectFieldsPresent = isset($subject['manageId']) && isset($subject['environment']);

        return $supportedAttribute && $subjectFieldsPresent;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // Administrator is always allowed to delete an entity
        if (in_array("ROLE_ADMINISTRATOR", $token->getRoles())) {
            return true;
        }

        // Fetch the entity and test if the team associated with the entity is one of the user's teams.
        $entity = $this->entityService->getManageEntityById($subject['manageId'], $subject['environment']);
        if (isset($entity['data']['metaDataFields']['coin:service_team_id'])) {
            $team = $entity['data']['metaDataFields']['coin:service_team_id'];
            $user = $token->getUser();
            return $user->isPartOfTeam($team);
        }
        return false;
    }
}
