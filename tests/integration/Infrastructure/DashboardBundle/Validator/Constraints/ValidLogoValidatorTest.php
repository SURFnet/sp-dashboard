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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidLogo;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidLogoValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidLogoValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ValidLogoValidator();
    }

    public function test_success_png()
    {
        $constraint = new ValidLogo();
        $this->validator->validate('file://' .__DIR__ . '/fixture/logo_validator/small.png', $constraint);

        $this->assertNoViolation();
    }

    public function test_success_gif()
    {
        $constraint = new ValidLogo();
        $this->validator->validate('file://' . __DIR__ . '/fixture/logo_validator/small.gif', $constraint);

        $this->assertNoViolation();
    }

    public function test_empty_value()
    {
        $constraint = new ValidLogo();
        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    public function test_invalid_image()
    {
        $constraint = new ValidLogo();
        $this->validator->validate('ufjd', $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('Logo is not a valid image.', $violation->getMessageTemplate());

    }

    public function test_invalid_type()
    {
        $constraint = new ValidLogo();
        $this->validator->validate(__DIR__ . '/fixture/logo_validator/image.jpg', $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('Logo should be a PNG or GIF.', $violation->getMessageTemplate());
    }
}
