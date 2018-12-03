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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use ReflectionMethod;
use ReflectionObject;
use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasAttributesValidator extends ConstraintValidator
{
    /**
     * @param Attribute    $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Command) {
            return;
        }

        if (!$this->hasAttributes($value)) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function hasAttributes(Command $command)
    {
        $reflection = new ReflectionObject($command);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (!$this->isAttributeGetter($method)) {
                continue;
            }

            $attribute = $method->invoke($command);
            if (!$attribute instanceof Attribute) {
                continue;
            }

            if ($attribute->isRequested()) {
                return true;
            }
        }

        return false;
    }

    private function isAttributeGetter(\ReflectionMethod $method)
    {
        return preg_match('/^get.*Attribute$/', $method->getName());
    }
}
