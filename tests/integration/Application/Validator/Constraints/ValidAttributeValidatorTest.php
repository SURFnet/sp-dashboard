<?php

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

namespace integration\Application\Validator\Constraints;

use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Application\Validator\Constraints\ValidAttributeValidator;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;
use Surfnet\ServiceProviderDashboard\Application\Validator\Constraints\ValidAttribute;
use Surfnet\ServiceProviderDashboard\Tests\Integration\Application\Validator\Constraints\mock;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidAttributeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ValidAttributeValidator
    {
        $attributeRepository = new AttributeRepository(__DIR__ . '/../fixture/attributes.json');
        $attributeService = new AttributeService($attributeRepository, 'en');
        return new ValidAttributeValidator($attributeService);
    }

    public function test_success_no_attributes()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate([], $constraint);

        $this->assertNoViolation();
    }

    public function test_success_empty_attribute()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate(['emailAddressAttribute' => null], $constraint);

        $this->assertNoViolation();
    }

    public function test_success_requested_attribute_with_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildAttributes(
            'emailAddressAttribute',
            true,
            'I really need this!'
        ), $constraint);

        $this->assertNoViolation();
    }

    public function test_success_not_requested_attribute_with_null_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildAttributes(
            'emailAddressAttribute',
            false,
            null
        ), $constraint);

        $this->assertNoViolation();
    }

    public function test_invalid_not_requested_attribute_with_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildAttributes(
            'emailAddressAttribute',
            false,
            'I really need this!'
        ), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_valid', $violation->getMessageTemplate());
    }

    public function test_invalid_requested_attribute_with_empty_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildAttributes(
            'emailAddressAttribute',
            true,
            ''
        ), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_valid', $violation->getMessageTemplate());
    }

    public function test_invalid_requested_attribute_with_null_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildAttributes(
            'emailAddressAttribute',
            true,
            null
        ), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_valid', $violation->getMessageTemplate());
    }

    public function test_invalid_non_existing_attribute()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildAttributes(
            'fantasyAttribute',
            true,
            'fantasy attribute'
        ), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_exists', $violation->getMessageTemplate());
    }

    private function buildAttributes(
        string $name,
        bool $requested,
        ?string $motivation
    ): array {
    
        $attributes = [];

        $attributes[$name] = new Attribute();
        $attributes[$name]
            ->setRequested($requested)
            ->setMotivation($motivation);

        return $attributes;
    }
}
