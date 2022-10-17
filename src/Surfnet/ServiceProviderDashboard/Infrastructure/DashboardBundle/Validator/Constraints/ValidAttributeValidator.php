<?php

declare(strict_types=1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidAttributeValidator extends ConstraintValidator
{
    /**
     * @var AttributeServiceInterface
     */
    private $attributeService;

    public function __construct(AttributeServiceInterface $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    private function buildAttributeViolation(string $placeholder, string $attributeName): void
    {
        $this->context->buildViolation(sprintf($placeholder, $attributeName))
            ->atPath('attribute')
            ->addViolation();
    }

    /**
     * @param array $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        foreach ($value ?? [] as $name => $attribute) {
            if (!isset($attribute)) {
                continue;
            }

            // We only want existing attributes
            if (!$this->attributeService->isKnownAttribute($name)) {
                $this->buildAttributeViolation($constraint->messageNotExists, $name);
            }

            // When an attribute is requested, we also need a motivation.
            if ($attribute->isRequested() && empty($attribute->getMotivation())) {
                $this->buildAttributeViolation($constraint->messageNotValid, $name);
            }

            // We don't want a non-requested attribute with a motivation. This would clutter Manage
            if (!$attribute->isRequested() && !empty($attribute->getMotivation())) {
                $this->buildAttributeViolation($constraint->messageNotValid, $name);
            }
        }
    }
}
