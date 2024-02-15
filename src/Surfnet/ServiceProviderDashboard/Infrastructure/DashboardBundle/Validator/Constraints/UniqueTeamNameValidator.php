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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Exception;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryTeamsRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTeamNameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly QueryTeamsRepository $queryService,
        private readonly string $urnPrefix,
    ) {
    }

    /**
     * @param  string $value
     * @throws Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        try {
            $result = $this->queryService->findTeamByUrn($this->urnPrefix . $value);
        } catch (Exception) {
            $this->context->addViolation('validator.team_name.registry_failure');
            return;
        }

        // Prevent creating services with existing team name in Teams.
        if ($result !== null && $result !== []) {
            $this->context->addViolation('validator.team_name.already_exists');
        }
    }
}
