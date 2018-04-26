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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Infrastructure\DashboardBundle\Validator\Constraints;

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidAttribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidAttributeValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidAttributeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ValidAttributeValidator();
    }

    public function test_success()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildContact(true, 'I really need this!'), $constraint);

        $this->assertNoViolation();
    }

    public function test_success_not_requested()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildContact(false, null), $constraint);

        $this->assertNoViolation();
    }

    public function test_invalid_not_requested_with_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildContact(false, 'I really need this!'), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_valid', $violation->getMessageTemplate());
    }

    public function test_invalid_attribute()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildContact(true, ''), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_valid', $violation->getMessageTemplate());
    }

    public function test_invalid_attribute_null_motivation()
    {
        $constraint = new ValidAttribute();
        $this->validator->validate($this->buildContact(true, null), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.attribute.not_valid', $violation->getMessageTemplate());
    }

    /**
     * @param bool $requested
     * @param string $motivation
     * @return Attribute
     */
    private function buildContact($requested, $motivation)
    {
        $contact = new Attribute();
        $contact
            ->setRequested($requested)
            ->setMotivation($motivation);

        return $contact;
    }
}
