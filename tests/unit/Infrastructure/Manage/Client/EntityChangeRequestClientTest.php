<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Manage\Client;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGeneratorStrategy;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\JiraTicketNumber;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\PublishMetadataRejectedException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\HttpClientInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\EntityChangeRequestClient;

class EntityChangeRequestClientTest extends MockeryTestCase
{
    public function testManageValidationErrorIsReportedAsRejection(): void
    {
        $client = $this->clientRespondingWith([
            'status' => 400,
            'error' => 'Bad Request',
            'validations' => '#/pathUpdates: extraneous key [coin:unknown] is not permitted in schema',
            'rawInput' => ['secret' => 's3cr3t-value'],
        ]);

        try {
            $this->openChangeRequest($client);
            self::fail('Expected the change request to be rejected');
        } catch (PublishMetadataRejectedException $e) {
            self::assertStringContainsString('extraneous key [coin:unknown]', $e->getMessage());
            self::assertStringNotContainsString('s3cr3t-value', $e->getMessage());
        }
    }

    public function testResponseWithoutIdOrValidationsIsAGenericFailure(): void
    {
        $client = $this->clientRespondingWith(['unexpected' => 'response']);

        try {
            $this->openChangeRequest($client);
            self::fail('Expected the change request to fail');
        } catch (PublishMetadataException $e) {
            self::assertNotInstanceOf(PublishMetadataRejectedException::class, $e);
            self::assertSame('Unable to open a entity change request in Manage', $e->getMessage());
        }
    }

    public function testChangeRequestWithIdIsReturned(): void
    {
        $client = $this->clientRespondingWith(['id' => '1234']);

        self::assertSame(['id' => '1234'], $this->openChangeRequest($client));
    }

    private function clientRespondingWith(array $response): EntityChangeRequestClient
    {
        $http = m::mock(HttpClientInterface::class);
        $http->shouldReceive('post')->andReturn($response);

        $generator = m::mock(JsonGeneratorStrategy::class);
        $generator->shouldReceive('generateEntityChangeRequest')->andReturn(['type' => 'oidc10_rp']);

        return new EntityChangeRequestClient($http, $generator, new NullLogger());
    }

    private function openChangeRequest(EntityChangeRequestClient $client): array
    {
        $entity = m::mock(ManageEntity::class);
        $entity->shouldReceive('getId')->andReturn('entity-id');
        $pristine = m::mock(ManageEntity::class);
        $pristine->shouldReceive('diff')->andReturn(new EntityDiff([], []));

        return $client->openChangeRequest(
            $entity,
            $pristine,
            m::mock(Contact::class),
            new JiraTicketNumber('CXT-1'),
        );
    }
}
