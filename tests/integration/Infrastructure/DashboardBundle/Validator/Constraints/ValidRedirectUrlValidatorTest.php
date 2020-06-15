<?php

/**
 * Copyright 2020 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidRedirectUrl;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidRedirectUrlValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidRedirectUrlValidatorTest extends ConstraintValidatorTestCase
{

    protected function createValidator()
    {
        return new ValidRedirectUrlValidator();
    }

    public function test_success()
    {
        $constraint = new ValidRedirectUrl();
        $command = m::mock(SaveOidcngEntityCommand::class);
        $command->makePartial();
        $command->shouldReceive('getClientId')->andReturn('https://www.example.com/');
        $this->mockFormData($command);

        $this->validator->validate('https://www.example.com/foo/bar', $constraint);
        $this->assertNoViolation();
    }

    /**
     * @dataProvider validReverseRedirectUrlsGenerator
     */
    public function test_success_reverse_redirect_url($clientId, $validReverseUrl)
    {
        $constraint = new ValidRedirectUrl();
        $command = m::mock(SaveOidcngEntityCommand::class);
        $command->makePartial();
        $command->shouldReceive('getClientId')->andReturn($clientId);
        $this->mockFormData($command);

        $this->validator->validate($validReverseUrl, $constraint);
        $this->assertNoViolation();
    }


    /**
     * @dataProvider invalidReverseRedirectUrlsGenerator
     */
    public function test_invalid_reverse_redirect_url($clientId, $validReverseUrl)
    {
        $constraint = new ValidRedirectUrl();
        $command = m::mock(SaveOidcngEntityCommand::class);
        $command->makePartial();
        $command->shouldReceive('getClientId')->andReturn($clientId);
        $this->mockFormData($command);

        $this->validator->validate($validReverseUrl, $constraint);
        $this->assertEquals(
            'validator.redirect_url.reverse_does_not_contain_client_id',
            $this->context->getViolations()->get(1)->getMessageTemplate()
        );
    }

    private function mockFormData(SaveEntityCommandInterface $data)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())->method('getData')->willReturn($data);
        $this->setRoot($form);
    }

    public static function validReverseRedirectUrlsGenerator()
    {
        yield ['https://example.com/', 'com.example://custom'];
        yield ['https://example.com/', 'com.example://http'];
        yield ['https://example.com/', 'com.example://https'];
        yield ['https://www.example.com/', 'com.example.www://https'];
        yield ['https://www.example.com/', 'com.example.www://https/foo/bar'];
        yield ['https://www.example.com/foob/bar', 'com.example.www://https/foo/bar'];
        yield ['https://www.example.com/foob/bar', 'com.example.www://https/foo/bar#fraction'];
        yield ['https://www.example.com/foob/bar', 'com.example.www://https/foo/bar?myQuery=param#fraction'];
    }

    public static function invalidReverseRedirectUrlsGenerator()
    {
        yield ['https://example.com/', 'com.example.test://https'];
        yield ['https://example.com/', 'com.example.test://custom'];
    }
}
