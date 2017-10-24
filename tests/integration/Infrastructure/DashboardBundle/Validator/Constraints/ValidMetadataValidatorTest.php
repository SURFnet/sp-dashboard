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

use Exception;
use Mockery as m;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidMetadata;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidMetadataValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidMetadataValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var ParserInterface|m\Mock
     */
    private $parser;

    /**
     * @var FetcherInterface|m\Mock
     */
    private $fetcher;

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->fetcher = m::mock(FetcherInterface::class);
        $this->parser = m::mock(ParserInterface::class);
        return new ValidMetadataValidator($this->fetcher, $this->parser);
    }

    public function test_success()
    {
        $xmlMetadata = file_get_contents(__DIR__ . '/fixture/metadata_validator/metadata.xml');

        $this->fetcher
            ->shouldReceive('fetch')
            ->andReturn($xmlMetadata);

        $this->parser
            ->shouldReceive('parseXml')
            ->with($xmlMetadata)
            ->andReturn(true);

        $this->validator->validate('https://domain.org/metadata', new ValidMetadata());

        $this->assertNoViolation();
    }

    public function test_empty_value()
    {
        $this->validator->validate(null, new ValidMetadata());

        $this->assertNoViolation();
    }

    public function test_invalid_metadata()
    {
        $xmlMetadata = file_get_contents(__DIR__ . '/fixture/metadata_validator/invalid_metadata.xml');

        $this->fetcher
            ->shouldReceive('fetch')
            ->andReturn($xmlMetadata);

        $this->parser
            ->shouldReceive('parseXml')
            ->with($xmlMetadata)
            ->andThrow(Exception::class, 'The metadata XML is invalid considering the associated XSD');

        $constraint = new ValidMetadata();
        $this->validator->validate('9j7hd6ijk5', $constraint);

        $this->assertNotCount(0, $this->context->getViolations());
    }
}
