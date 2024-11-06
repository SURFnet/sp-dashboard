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

use Mockery as m;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoInvalidTypeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoNotFoundException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\LogoValidationHelperInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidLogo;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidLogoValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidLogoValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var LogoValidationHelperInterface|Mock
     */
    private $validationHelper;

    protected function createValidator()
    {
        $this->validationHelper = m::mock(LogoValidationHelperInterface::class);

        return new ValidLogoValidator($this->validationHelper);
    }

    public function test_success_png()
    {
        $constraint = new ValidLogo();

        $this->validationHelper
            ->shouldReceive('validateLogo')
            ->andReturn('file://'.__DIR__.'/fixture/logo_validator/small.png');

        $this->validator->validate('file://'.__DIR__.'/fixture/logo_validator/small.png', $constraint);

        $this->assertNoViolation();
    }

    public function test_success_gif()
    {
        $constraint = new ValidLogo();

        $this->validationHelper
            ->shouldReceive('validateLogo')
            ->andReturn('file://'.__DIR__.'/fixture/logo_validator/small.gif');

        $this->validator->validate('file://'.__DIR__.'/fixture/logo_validator/small.gif', $constraint);

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

        $this->validationHelper
            ->shouldReceive('validateLogo')
            ->andReturn('file://'.__DIR__.'/fixture/logo_validator/ufjd');

        $this->validator->validate('ufjd', $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.logo.not_an_image', $violation->getMessageTemplate());
    }

    public function test_invalid_type()
    {
        $constraint = new ValidLogo();

        $this->validationHelper
            ->shouldReceive('validateLogo')
            ->andThrow(LogoInvalidTypeException::class);

        $this->validator->validate(__DIR__.'/fixture/logo_validator/image.jpg', $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.logo.wrong_type', $violation->getMessageTemplate());
    }

    public function test_unable_to_download()
    {
        $constraint = new ValidLogo();

        $this->validationHelper
            ->shouldReceive('validateLogo')
            ->andThrow(LogoNotFoundException::class);

        $this->validator->validate('https://example.com/foobar.jpg', $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.logo.download_failed', $violation->getMessageTemplate());
    }
}
