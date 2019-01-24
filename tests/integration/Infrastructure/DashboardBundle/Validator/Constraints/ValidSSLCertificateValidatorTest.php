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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidSSLCertificate;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidSSLCertificateValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidSSLCertificateValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ValidSSLCertificateValidator();
    }

    public function test_success()
    {
        $cert = file_get_contents(__DIR__ . '/fixture/certificate_validator/certificate.cer');
        $this->validator->validate($cert, new ValidSSLCertificate());

        $this->assertNoViolation();
    }

    public function test_key_without_envelope()
    {
        $cert = file_get_contents(__DIR__ . '/fixture/certificate_validator/certificate_without_envelope.cer');
        $this->validator->validate($cert, new ValidSSLCertificate());

        $this->assertNoViolation();
    }

    public function test_empty_value()
    {
        $this->validator->validate(null, new ValidSSLCertificate());

        $this->assertNoViolation();
    }

    public function test_invalid_key()
    {
        $constraint = new ValidSSLCertificate();

        $cert = file_get_contents(__DIR__ . '/fixture/certificate_validator/invalid.cer');
        $this->validator->validate($cert, $constraint);

        $violations = $this->context->getViolations();

        $this->assertNotEmpty($violations);
        $this->assertEquals('validator.ssl_certificate.not_valid', $violations->get(0)->getMessageTemplate());
    }

    public function test_invalid_key_length()
    {
        $cert = file_get_contents(__DIR__ . '/fixture/certificate_validator/google.cer');
        $this->validator->validate($cert, new ValidSSLCertificate());

        $violations = $this->context->getViolations();

        $this->assertNotEmpty($violations);
        $this->assertEquals(
            'validator.ssl_certificate.wrong_key_length',
            $violations->get(0)->getMessageTemplate()
        );
    }
}
