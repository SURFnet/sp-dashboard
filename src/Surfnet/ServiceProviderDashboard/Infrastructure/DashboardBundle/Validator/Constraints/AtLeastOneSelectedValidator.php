<?php

/**
 * Copyright 2024 SURFnet B.V.
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

declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AtLeastOneSelectedValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AtLeastOneSelected) {
            throw new UnexpectedTypeException($constraint, AtLeastOneSelected::class);
        }

        foreach ($constraint->fieldNames as $fieldName) {
            if (!isset($value->{$fieldName}) || !is_array($value->{$fieldName})) {
                throw new InvalidArgumentException('$value must have array field with name: "' . $fieldName. '"');
            }

            if (!empty(($value->{$fieldName}))) {
                return;
            }
        }

        $this->context->addViolation('validator.entity.idps.institution-idps');
    }
}
