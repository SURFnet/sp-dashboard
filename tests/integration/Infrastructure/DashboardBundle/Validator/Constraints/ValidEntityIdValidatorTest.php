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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EditEntityCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidEntityId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints\ValidEntityIdValidator;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ValidEntityIdValidatorTest extends ConstraintValidatorTestCase
{

    /**
     * @var MockHandler
     */
    private $mockHandler;

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    protected function createValidator()
    {
        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);
        $client = new QueryClient(new HttpClient($guzzle));

        return new ValidEntityIdValidator($client);
    }

    public function test_success()
    {
        // An empty array response is Manage's way of telling you there where no results, in this case there is no
        // record in the service registry with the given entityId.
        $this->mockHandler->append(new Response(200, [], '[]'));

        $entityCommand = m::mock(EditEntityCommand::class);
        $entityCommand->shouldReceive('isDraft')->andReturn(true);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('https://www.domain.org');
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);

        $this->setRoot($entityCommand);

        $this->validator->validate('https://sub.domain.org', new ValidEntityId());

        $this->assertNoViolation();
    }

    public function test_empty_entity_id()
    {
        $entityCommand = m::mock(EditEntityCommand::class);
        $entityCommand->shouldReceive('isDraft')->andReturn(true);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('https://www.domain.org');

        $this->setRoot($entityCommand);

        $this->validator->validate(null, new ValidEntityId());

        $this->assertNoViolation();
    }

    public function test_empty_metadata_url()
    {
        $entityCommand = m::mock(EditEntityCommand::class);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('');
        $entityCommand->shouldReceive('isDraft')->andReturn(true);

        $this->setRoot($entityCommand);

        $this->validator->validate('domain.org', new ValidEntityId());

        $this->assertNoViolation();
    }

    public function test_invalid_domain()
    {
        $domainA = 'invalid domain\.com';
        $domainB = 'domain.org';

        $entityCommand = m::mock(EditEntityCommand::class);
        $entityCommand->shouldReceive('isDraft')->andReturn(true);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('https:///www.' . $domainA);
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);

        $this->setRoot($entityCommand);

        $constraint = new ValidEntityId();
        $this->validator->validate('https://sub.' . $domainB, $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'Invalid metadataUrl.',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_invalid_entity_id_url()
    {
        $entityCommand = m::mock(EditEntityCommand::class);

        $entityCommand->shouldReceive('isDraft')->andReturn(true);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('www.domain.org');
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);
        $this->setRoot($entityCommand);

        $constraint = new ValidEntityId();
        $this->validator->validate('q$:\₪.3%$', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'Invalid entityId.',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }

    public function test_invalid_metadata_url()
    {
        $entityCommand = m::mock(EditEntityCommand::class);

        $entityCommand->shouldReceive('isDraft')->andReturn(true);
        $entityCommand->shouldReceive('getMetadataUrl')->andReturn('q$:\₪.3%$');
        $entityCommand->shouldReceive('isForProduction')->andReturn(false);

        $this->setRoot($entityCommand);

        $constraint = new ValidEntityId();
        $this->validator->validate('domain.org', $constraint);

        $violations = $this->context->getViolations();
        $this->assertCount(1, $violations);
        $this->assertEquals(
            'Invalid metadataUrl.',
            $violations->get(0)->getMessageTemplate(),
            'Expected certain violation but dit not receive it.'
        );
    }
}
