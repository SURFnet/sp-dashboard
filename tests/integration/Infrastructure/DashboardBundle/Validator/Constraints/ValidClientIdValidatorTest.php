<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidClientId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidClientIdValidator;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidEntityId;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidClientIdValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var EntityRepository
     */
    private $repository;

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->mockHandler = new MockHandler();
        $this->repository = m::mock(EntityRepository::class);

        $guzzle = new Client(['handler' => $this->mockHandler]);
        $client = new QueryClient(new HttpClient($guzzle, new NullLogger()));

        return new ValidClientIdValidator(
            $client,
            $client,
            $this->repository
        );
    }

    public function test_success()
    {
        // An empty array response is Manage's way of telling you there where no results, in this case there is no
        // record in the service registry with the given entityId.
        $this->mockHandler->append(new Response(200, [], '[]'));

        $entityCommand = m::mock(SaveOidcEntityCommand::class);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('https://www.domain.org');
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $entityCommand->shouldReceive('getId')->andReturn(1);

        $this->setRoot($entityCommand);

        $this->repository->shouldReceive('findById')
            ->andReturn(null);

        $this->validator->validate('https://sub.domain.org', new ValidClientId());

        $this->assertNoViolation();
    }

    public function test_success_for_production()
    {
        // An empty array response is Manage's way of telling you there where no results, in this case there is no
        // record in the service registry with the given entityId.
        $this->mockHandler->append(new Response(200, [], '[]'));

        $entityCommand = m::mock(SaveOidcEntityCommand::class);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('https://www.domain.org');
        $entityCommand->shouldReceive('isForProduction')->andReturn(true);
        $entityCommand->shouldReceive('getId')->andReturn(1);

        $this->setRoot($entityCommand);

        $this->repository->shouldReceive('findById')
            ->andReturn(null);

        $this->validator->validate('https://sub.domain.org', new ValidClientId());

        $this->assertNoViolation();
    }

    public function test_empty_client_id()
    {
        $entityCommand = m::mock(SaveOidcEntityCommand::class);

        $this->setRoot($entityCommand);

        $this->validator->validate(null, new ValidClientId());

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.client_id.empty',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_invalid_client_id_url()
    {
        $entityCommand = m::mock(SaveOidcEntityCommand::class);

        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $this->setRoot($entityCommand);

        $constraint = new ValidClientId();
        $this->validator->validate('q$:\₪.3%$', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'validator.client_id.invalid_url',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }
}
