<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidAttributeValidator extends ConstraintValidator
{
    /**
     * @param Attribute    $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        // When an attribute is requested, we also need a motivation.
        if ($value->isRequested() && empty($value->getMotivation())) {
            $this->context->addViolation($constraint->message);
        }

        // We don't want a non requested attribute with a motivation. This would clutter Manage
        if (!$value->isRequested() && !empty($value->getMotivation())) {
            $this->context->addViolation($constraint->message);
        }
    }
}
