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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidContact;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidContactValidator;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidLogo;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidContactValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new ValidContactValidator();
    }

    public function test_success()
    {
        $constraint = new ValidContact();
        $this->validator->validate($this->buildContact('John', 'Doe', 'john@doe.com', '0625368948'), $constraint);

        $this->assertNoViolation();
    }

    public function test_invalid_contact()
    {
        $constraint = new ValidContact();
        $this->validator->validate($this->buildContact('John', null, null, null), $constraint);

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);

        $this->assertEquals('validator.contact.not_valid', $violation->getMessageTemplate());
    }

    private function buildContact($firstName, $lastName, $email, $phone)
    {
        $contact = new Contact();
        $contact
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhone($phone);
        return $contact;
    }
}
