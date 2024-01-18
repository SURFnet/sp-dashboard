<?php

//declare(strict_types = 1);

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
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\QueryClient;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UrnFormattedTeamNameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly string $defaultStemName,
        private readonly string $groupName
    ) {
    }

    /**
     * @param  string $value
     * @throws Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        $teamName = $value;
        $hasDefaultStemName = strpos($teamName, $this->defaultStemName);
        $hasGroupName = strpos($teamName, $this->groupName);

        // we cannot use if($hasDefaultStemName) because strpos can return the int 0, meaning it occurs at index 0
        if ($hasDefaultStemName === false || $hasGroupName === false) {
            $this->context->addViolation('validator.team_name.not_in_urn_format');
        }
    }
}
