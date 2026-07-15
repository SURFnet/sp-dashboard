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

use GuzzleHttp\Handler\MockHandler;
use Mockery as m;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\HostBlocklistCheckerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidMetadataUrl;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidMetadataUrlValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidMetadataUrlValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var HostBlocklistCheckerInterface|Mock
     */
    private $hostBlocklistChecker;

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->mockHandler = new MockHandler();
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('isBlocked')->andReturn(false)->byDefault();

        return new ValidMetadataUrlValidator($this->hostBlocklistChecker, false);
    }

    public function test_success()
    {
        $this->validator->validate('https://www.domain.org', new ValidMetadataUrl());

        $this->assertNoViolation();
    }


    public function test_invalid_domain()
    {
        $this->validator->validate('https:///invalid\.com', new ValidMetadataUrl());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.invalid_url',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_private_host_is_blocked()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldReceive('isBlocked')->andReturn(true);
        $this->validator = new ValidMetadataUrlValidator($this->hostBlocklistChecker, false);
        $this->validator->initialize($this->context);

        $this->validator->validate('http://169.254.169.254/metadata', new ValidMetadataUrl());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.entity_id.private_host',
            $violations->get(0)->getMessageTemplate()
        );
    }

    public function test_allow_metadata_private_hosts_skips_check()
    {
        $this->hostBlocklistChecker = m::mock(HostBlocklistCheckerInterface::class);
        $this->hostBlocklistChecker->shouldNotReceive('isBlocked');
        $this->validator = new ValidMetadataUrlValidator($this->hostBlocklistChecker, true);
        $this->validator->initialize($this->context);

        $this->validator->validate('http://169.254.169.254/metadata', new ValidMetadataUrl());

        $this->assertNoViolation();
    }
}
