<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use Exception;
use Mockery as m;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\UniqueRedirectUrls;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\UniqueRedirectUrlsValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueRedirectUrlsValidatorTest extends ConstraintValidatorTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        return new UniqueRedirectUrlsValidator();
    }

    public function test_success()
    {
        $saveEntityCommand = m::mock(SaveOidcngEntityCommand::class);
        $this->mockFormData($saveEntityCommand);

        $redirectUris = ['https://example.org/redirect1', 'https://example.org/redirect2'];

        $this->validator->validate($redirectUris, new UniqueRedirectUrls());

        $this->assertNoViolation();
    }

    public function test_duplicates_are_rejected()
    {
        $saveEntityCommand = m::mock(SaveOidcngEntityCommand::class);
        $this->mockFormData($saveEntityCommand);

        $redirectUris = [
            'https://example.org/redirect1',
            'https://example.org/redirect2',
            'https://example.org/redirect1',
        ];

        $this->validator->validate($redirectUris, new UniqueRedirectUrls());

        $violations = $this->context->getViolations();
        $violation = $violations->get(0);
        $this->assertEquals('validator.unique_redirect_urls.duplicate_found', $violation->getMessageTemplate());
    }

    public function test_only_oidc_commands_are_processed()
    {
        $saveEntityCommand = m::mock(SaveSamlEntityCommand::class);
        $this->mockFormData($saveEntityCommand);

        $redirectUris = ['https://example.org/redirect1'];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid validator command exception');
        $this->validator->validate($redirectUris, new UniqueRedirectUrls());
    }

    private function mockFormData(SaveEntityCommandInterface $data)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())->method('getData')->willReturn($data);

        $this->setRoot($form);
    }
}
