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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\CommandHandler\Entity;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity\PublishEntityCommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Factory\MotivationMetadataFactory;
use Surfnet\ServiceProviderDashboard\Application\Factory\PrivacyQuestionsMetadataFactory;
use Surfnet\ServiceProviderDashboard\Application\Factory\SpDashboardMetadataFactory;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\HttpClient;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PublishEntityCommandHandlerTest extends MockeryTestCase
{

    /**
     * @var PublishEntityCommandHandler
     */
    private $commandHandler;

    /**
     * @var EntityRepository|Mock
     */
    private $repository;

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var FlashBagInterface|Mock
     */
    private $flashBag;

    /**
     * @var GeneratorInterface|Mock
     */
    private $generator;

    /**
     * @var PrivacyQuestionsMetadataFactory|Mock
     */
    private $privacyQuestionsFactory;

    /**
     * @var MotivationMetadataFactory|Mock
     */
    private $motivationMetadataFactory;

    /**
     * @var SpDashboardMetadataFactory|Mock
     */
    private $spDashboardMetadataFactory;

    /**
     * @var PublishEntityClient
     */
    private $client;

    public function setUp()
    {
        $this->repository = m::mock(EntityRepository::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->mockHandler = new MockHandler();
        $guzzle = new Client(['handler' => $this->mockHandler]);

        $this->generator = m::mock(GeneratorInterface::class);

        $client = new PublishEntityClient(new HttpClient($guzzle), $this->generator, $this->logger);

        $this->client = m::mock($client)->makePartial();

        $this->flashBag = m::mock(FlashBagInterface::class);

        $this->privacyQuestionsFactory = m::mock(PrivacyQuestionsMetadataFactory::class);
        $this->motivationMetadataFactory = m::mock(MotivationMetadataFactory::class);
        $this->spDashboardMetadataFactory = m::mock(SpDashboardMetadataFactory::class);

        $this->commandHandler = new PublishEntityCommandHandler(
            $this->repository,
            $this->client,
            $this->privacyQuestionsFactory,
            $this->motivationMetadataFactory,
            $this->spDashboardMetadataFactory,
            $this->logger,
            $this->flashBag
        );


        parent::setUp();
    }

    public function test_it_can_publish_to_manage()
    {
        $xml = file_get_contents(__DIR__.'/fixture/metadata.xml');

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getEntityId')
            ->once()
            ->andReturn('1');

        $entity
            ->shouldReceive('getMetadataXml')
            ->andReturn($xml);

        $entity
            ->shouldReceive('getNameNl')
            ->andReturn('Test Entity Name');

        $entity
            ->shouldReceive('getComments')
            ->andReturn('Lorem ipsum dolor sit');

        $entity
            ->shouldReceive('getMetadataUrl')
            ->andReturn('https://fobar.example.com');

        $entity
            ->shouldReceive('hasComments')
            ->once()
            ->andReturn(true);

        $entity
            ->shouldReceive('getManageId')
            ->once()
            ->andReturn(null);

        $this->generator
            ->shouldReceive('generate')
            ->andReturn($xml);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->logger
            ->shouldReceive('info')
            ->times(3);

        $this->privacyQuestionsFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->motivationMetadataFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->spDashboardMetadataFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->mockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        $this->mockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $command = new PublishEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_push()
    {
        $xml = file_get_contents(__DIR__.'/fixture/metadata.xml');

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getEntityId')
            ->once()
            ->andReturn('1');

        $entity
            ->shouldReceive('getMetadataXml')
            ->andReturn($xml);

        $entity
            ->shouldReceive('getComments')
            ->andReturn('Lorem ipsum dolor sit');

        $entity
            ->shouldReceive('getNameNl')
            ->andReturn('Test Entity Name');

        $entity
            ->shouldReceive('hasComments')
            ->once()
            ->andReturn(true);

        $entity
            ->shouldReceive('getMetadataUrl')
            ->andReturn('https://fobar.example.com');

        $entity
            ->shouldReceive('getManageId')
            ->once()
            ->andReturn(null);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->logger
            ->shouldReceive('info')
            ->times(4);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.push');

        $this->generator
            ->shouldReceive('generate')
            ->andReturn($xml);

        $this->privacyQuestionsFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->motivationMetadataFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->spDashboardMetadataFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->mockHandler->append(new Response(200, [], '{"id": "d6f394b2-08b1-4882-8b32-81688c15c489"}'));
        $this->mockHandler->append(new Response(200, [], '{"id": "d6f394b2-08b1-4882-8b32-81688c15c489"}'));
        $this->mockHandler->append(new Response(418));

        $command = new PublishEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }

    public function test_it_handles_failing_publish()
    {
        $xml = file_get_contents(__DIR__.'/fixture/metadata.xml');

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getMetadataXml')
            ->andReturn($xml);

        $entity
            ->shouldReceive('getNameNl')
            ->andReturn('Test Entity Name');

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $entity
            ->shouldReceive('getEntityId')
            ->once()
            ->andReturn('1');

        $entity
            ->shouldReceive('getManageId')
            ->once()
            ->andReturn(null);

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $this->logger
            ->shouldReceive('error')
            ->times(1);

        $this->flashBag
            ->shouldReceive('add')
            ->with('error', 'entity.edit.error.publish');

        $this->generator
            ->shouldReceive('generate')
            ->andReturn($xml);

        $this->privacyQuestionsFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->motivationMetadataFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->spDashboardMetadataFactory
            ->shouldReceive('build')
            ->andReturn([]);

        $this->mockHandler->append(new Response(418));

        $command = new PublishEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }

    public function test_it_saves_additional_metadata()
    {
        $xml = file_get_contents(__DIR__.'/fixture/metadata.xml');

        $entity = m::mock(Entity::class);
        $entity
            ->shouldReceive('getMetadataXml')
            ->andReturn($xml);

        $entity
            ->shouldReceive('getNameNl')
            ->andReturn('Test Entity Name');

        $entity
            ->shouldReceive('getComments')
            ->andReturn('Lorem ipsum dolor sit');

        $this->generator
            ->shouldReceive('generate')
            ->andReturn($xml);

        $this->repository
            ->shouldReceive('findById')
            ->with('d6f394b2-08b1-4882-8b32-81688c15c489')
            ->andReturn($entity);

        $this->logger
            ->shouldReceive('info')
            ->times(2);

        $this->privacyQuestionsFactory
            ->shouldReceive('build')
            ->with($entity)
            ->andReturn([
                'metaDataFields.coin:privacy:certification' => '1',
                'metaDataFields.coin:privacy:certification_valid_to' => '1484392832',
                'metaDataFields.coin:privacy:what_data' => 'Text',
            ]);

        $this->motivationMetadataFactory
            ->shouldReceive('build')
            ->with($entity)
            ->andReturn([
                'metaDataFields.coin:attr_motivation:eduPersonTargetedID' => 'Text',
                'metaDataFields.coin:attr_motivation:uid' => 'Text',
            ]);

        $this->spDashboardMetadataFactory
            ->shouldReceive('build')
            ->with($entity)
            ->andReturn([
                'metaDataFields.coin:service_team_id' => 'urn.my.sample.team',
                'metaDataFields.coin:original_metadata_url' => 'http://example.com/metadata',
            ]);

        $this->client
            ->shouldReceive('publish')
            ->with(
                $entity,
                array_merge(
                    [
                        'metaDataFields.coin:privacy:certification' => '1',
                        'metaDataFields.coin:privacy:certification_valid_to' => '1484392832',
                        'metaDataFields.coin:privacy:what_data' => 'Text',
                    ],
                    [
                        'metaDataFields.coin:attr_motivation:eduPersonTargetedID' => 'Text',
                        'metaDataFields.coin:attr_motivation:uid' => 'Text',
                    ],
                    [
                        'metaDataFields.coin:service_team_id' => 'urn.my.sample.team',
                        'metaDataFields.coin:original_metadata_url' => 'http://example.com/metadata',
                    ]
                )
            )->andReturn(json_decode('{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}', true));

        $this->mockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $command = new PublishEntityCommand('d6f394b2-08b1-4882-8b32-81688c15c489');
        $this->commandHandler->handle($command);
    }
}
