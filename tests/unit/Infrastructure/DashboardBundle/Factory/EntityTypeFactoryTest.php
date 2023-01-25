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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Mailer;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\EntityTypeFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OidcngEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OidcngResourceServerEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\SamlEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory\SaveCommandFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory;

class EntityTypeFactoryTest extends MockeryTestCase
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var EntityTypeFactory
     */
    private $factory;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var FormType
     */
    private $form;
    /**
     * @var SaveCommandFactoryInterface&m\Mock
     */
    private $saveCommandFactory;

    protected function setUp()
    {
        $this->formFactory = m::mock(FormFactory::class);
        $this->saveCommandFactory = m::mock(SaveCommandFactoryInterface::class);
        $this->service = m::mock(Service::class);
        $this->form = m::mock(FormType::class);
        $this->attributeService = m::mock(AttributeServiceInterface::class);
        $this->factory = new EntityTypeFactory($this->formFactory, $this->saveCommandFactory, $this->attributeService);
    }

    public function test_build_create_new_saml_form()
    {
        $this->formFactory
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($entityType) {
                $this->assertSame(SamlEntityType::class, $entityType);
                return true;
            }), \Mockery::on(function ($command) {
                $this->assertInstanceOf(SaveSamlEntityCommand::class, $command);
                return true;
            }), \Mockery::on(function ($options) {
                $this->assertSame([
                    'validation_groups' => [
                        0 => 'Default',
                        1 => 'production',
                    ],
                ], $options);
                return true;
            }))
            ->once()
            ->andReturn($this->form);

        $form = $this->factory->createCreateForm(
            Constants::TYPE_SAML,
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertInstanceOf(FormType::class, $form);
    }

    public function test_build_create_new_saml_form_from_entity()
    {
        $this->formFactory
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($entityType) {
                $this->assertSame(SamlEntityType::class, $entityType);
                return true;
            }), \Mockery::on(function ($command) {
                $this->assertInstanceOf(SaveSamlEntityCommand::class, $command);
                return true;
            }), \Mockery::on(function ($options) {
                $this->assertSame([
                    'validation_groups' => [
                        0 => 'Default',
                        1 => 'production',
                    ],
                ], $options);
                return true;
            }))
            ->once()
            ->andReturn($this->form);

        $form = $this->factory->createCreateForm(
            Constants::TYPE_SAML,
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertInstanceOf(FormType::class, $form);
    }

    public function test_build_create_new_oidcng_form()
    {
        $this->formFactory
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($entityType) {
                $this->assertSame(OidcngEntityType::class, $entityType);
                return true;
            }), \Mockery::on(function ($command) {
                $this->assertInstanceOf(SaveOidcngEntityCommand::class, $command);
                return true;
            }), \Mockery::on(function ($options) {
                $this->assertSame([
                    'validation_groups' => [
                        0 => 'Default',
                        1 => 'production',
                    ],
                ], $options);
                return true;
            }))
            ->once()
            ->andReturn($this->form);

        $form = $this->factory->createCreateForm(
            Constants::TYPE_OPENID_CONNECT_TNG,
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertInstanceOf(FormType::class, $form);
    }


    public function test_build_create_new_oidcng_form_from_entity()
    {
        $this->formFactory
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($entityType) {
                $this->assertSame(OidcngEntityType::class, $entityType);
                return true;
            }), \Mockery::on(function ($command) {
                $this->assertInstanceOf(SaveOidcngEntityCommand::class, $command);
                return true;
            }), \Mockery::on(function ($options) {
                $this->assertSame([
                    'validation_groups' => [
                        0 => 'Default',
                        1 => 'production',
                    ],
                ], $options);
                return true;
            }))
            ->once()
            ->andReturn($this->form);

        $form = $this->factory->createCreateForm(
            Constants::TYPE_OPENID_CONNECT_TNG,
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertInstanceOf(FormType::class, $form);
    }

    public function test_build_create_new_oauth20_rs_form()
    {
        $this->formFactory
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($entityType) {
                    $this->assertSame(OidcngResourceServerEntityType::class, $entityType);
                    return true;
                }), \Mockery::on(function ($command) {
                    $this->assertInstanceOf(SaveOidcngResourceServerEntityCommand::class, $command);
                    return true;
                }), \Mockery::on(function ($options) {
                    $this->assertSame([
                        'validation_groups' => [
                            0 => 'Default',
                            1 => 'production',
                        ],
                    ], $options);
                    return true;
                }))
            ->once()
            ->andReturn($this->form);

        $form = $this->factory->createCreateForm(
            Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER,
            $this->service,
            Constants::ENVIRONMENT_PRODUCTION
        );

        $this->assertInstanceOf(FormType::class, $form);
    }

    /**
     *  @dataProvider providePublishOrChangeOptions
     */
    public function test_build_edit_saml_form_from_entity(
        $isCopy,
        $manageEntityStatus,
        $environment,
        $expectation
    ) {
        $manageEntity = m::mock(ManageEntity::class);

        /**
         * Make ManageEntity partial to test method isRequestedProductionEntity
         */
        $manageEntity->makePartial();

        $manageEntity
            ->shouldReceive('getProtocol->getProtocol')
            ->andReturn(Constants::TYPE_SAML);

        $manageEntity
            ->shouldReceive('getEnvironment')
            ->andReturn($environment);

        $manageEntity
            ->shouldReceive('getStatus')
            ->andReturn($manageEntityStatus);

        $saveSamlEntityCommand = m::mock(SaveSamlEntityCommand::class);
        $saveSamlEntityCommand
            ->shouldReceive('setService');

        $this->saveCommandFactory
            ->shouldReceive('buildSamlCommandByManageEntity')
            ->with($manageEntity, $environment)
            ->andReturn($saveSamlEntityCommand);

        $this->formFactory
            ->shouldReceive('create')
            ->with(\Mockery::on(function ($entityType) {
                    static::assertSame(SamlEntityType::class, $entityType);
                    return true;
                }), \Mockery::on(function ($command) {
                    static::assertInstanceOf(SaveSamlEntityCommand::class, $command);
                    return true;
                }), \Mockery::on(function ($options) use ($expectation) {
                    static::assertSame($expectation, $options);
                    return true;
                }))
            ->once()
            ->andReturn($this->form);

        $form = $this->factory->createEditForm(
            $manageEntity,
            $this->service,
            $environment,
            $isCopy
        );

        $this->assertInstanceOf(FormType::class, $form);
    }

    public static function providePublishOrChangeOptions(): array
    {
        return [
            'new entity' => [
                false,
                Constants::STATE_PUBLICATION_REQUESTED,
                Constants::ENVIRONMENT_PRODUCTION,
                [
                    'validation_groups' => ['Default', 'production'],
                ]
            ],
            'new entity copy' => [
                true,
                Constants::STATE_PUBLICATION_REQUESTED,
                Constants::ENVIRONMENT_PRODUCTION,
                [
                    'validation_groups' => ['Default', 'production'],
                ]
            ],
            'existing published entity' => [
                false,
                Constants::STATE_PUBLISHED,
                Constants::ENVIRONMENT_PRODUCTION,
                [
                    'validation_groups' => ['Default', 'production'],
                    'publish_button_label' => 'entity.edit.label.change',
                ]
            ],
            'copy entity ignore state and environment' => [
                true,
                Constants::STATE_PUBLISHED,
                Constants::ENVIRONMENT_PRODUCTION,
                [
                    'validation_groups' => ['Default', 'production'],
                ]
            ],
            'new test entity' => [
                false,
                Constants::STATE_PUBLISHED,
                Constants::ENVIRONMENT_TEST,
                [
                ]
            ],
        ];
    }
}
