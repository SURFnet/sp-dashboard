<?php

/**
 * Copyright 2024 SURFnet B.V.
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

declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Infrastructure\DashboardBundle\Validator\Constraints;


use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\AtLeastOneSelected;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\AtLeastOneSelectedValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AtLeastOneSelectedValidatorTest extends TestCase
{
    private $context;
    private $validator;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new AtLeastOneSelectedValidator();
        $this->validator->initialize($this->context);
    }

    public function testNoFieldSelected(): void
    {
        $constraint = new AtLeastOneSelected(fieldNames: ['field1', 'field2']);
        $value = (object) ['field1' => [], 'field2' => []];

        $this->context->expects($this->once())
            ->method('addViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testOneFieldSelected(): void
    {
        $constraint = new AtLeastOneSelected(fieldNames: ['field1', 'field2']);
        $value = (object) ['field1' => [0], 'field2' => ''];

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testAllFieldsSelected(): void
    {
        $constraint = new AtLeastOneSelected(fieldNames: ['field1', 'field2']);
        $value = (object) ['field1' => [1], 'field2' => [2]];

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }
}