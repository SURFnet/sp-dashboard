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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Assembler;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Surfnet\ServiceProviderDashboard\Application\Assembler\ServiceStatusAssembler;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Component\Routing\RouterInterface;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity as EntityViewObject;
use Symfony\Component\Translation\TranslatorInterface;

class ServiceStatusAssemblerTest extends MockeryTestCase
{
    /** @var Service|MockInterface */
    private $service;

    /** @var ServiceStatusService|MockInterface */
    private $serviceStatusService;

    /** @var RouterInterface|MockInterface */
    private $router;

    /** @var TranslatorInterface|MockInterface */
    private $translator;

    public function setUp()
    {
        $this->service = m::mock(Service::class);
        $this->serviceStatusService = m::mock(ServiceStatusService::class);
        $this->router = m::mock(RouterInterface::class);
        $this->translator = m::mock(TranslatorInterface::class);

        $this->router
            ->shouldReceive('generate')
            ->andReturnUsing(
                function ($route, $parameters) {
                    return $route.'?'.http_build_query($parameters);
                }
            );

        $this->translator
            ->shouldReceive('trans')
            ->andReturnUsing(
                function ($id, $parameters = array()) {
                    return $id . (count($parameters) ? '%'. implode('%', $parameters) : '');
                }
            );
    }

    /**
     * @dataProvider assemblerProvider
     */
    public function test_it_can_return_the_status_of_a_service($serviceData, $statusServiceData, $expectedJson)
    {
        $this->createServiceMock($serviceData);
        $this->createStatusServiceMock($statusServiceData);

        $assembler = new ServiceStatusAssembler(
            $this->service,
            $this->serviceStatusService,
            $this->translator
        );

        $result = $assembler->getDto();
        $json = json_encode($result);

        $expectedJson = json_encode(json_decode($expectedJson));

        $this->assertSame($expectedJson, $json);
    }

    public static function assemblerProvider()
    {
        return [
            'institute-output' => [
                'service' => [
                    'id' => 2,
                    'name' => 'service-2',
                    'type' => Service::SERVICE_TYPE_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_YES,
                    'representativeApproved' => Service::SURFCONEXT_APPROVED_YES,
                    'contractSigned' => null,
                    'isPrivacyQuestionsEnabled' => true,
                    'connectionStatus' => Service::CONNECTION_STATUS_ACTIVE,
                    'entities' => [
                        ['id' => 1, 'name' => 'entity-1', 'environment' => Entity::ENVIRONMENT_PRODUCTION],
                    ],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_YES,
                    'privacyQuestions' => true,
                ],
                'result' => '{
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "representative-approved": "success",
    "privacy-questions": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "intake-conducted": "service.overview.progress.tooltip.intake-conducted.success.html",
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.success.html",
    "representative-approved": "service.overview.progress.tooltip.representative-approved.success.html",
    "privacy-questions": "service.overview.progress.tooltip.privacy-questions.success.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.success.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 100
}
'
            ],
            'non-institute-output' => [
                'service' => [
                    'id' => 1,
                    'name' => 'service-1',
                    'type' => Service::SERVICE_TYPE_NON_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_YES,
                    'representativeApproved' => null,
                    'contractSigned' => Service::CONTRACT_SIGNED_YES,
                    'isPrivacyQuestionsEnabled' => true,
                    'connectionStatus' => Service::CONNECTION_STATUS_ACTIVE,
                    'entities' => [
                        ['id' => 1, 'name' => 'entity-1', 'environment' => Entity::ENVIRONMENT_PRODUCTION],
                    ],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_YES,
                    'privacyQuestions' => true,
                ],
                'result' => '{
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "contract-signed": "success",
    "privacy-questions": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "intake-conducted": "service.overview.progress.tooltip.intake-conducted.success.html",
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.success.html",
    "contract-signed": "service.overview.progress.tooltip.contract-signed.success.html",
    "privacy-questions": "service.overview.progress.tooltip.privacy-questions.success.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.success.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 100
}'
            ],
            'entity-links' => [
                'service' => [
                    'id' => 2,
                    'name' => 'service-2',
                    'type' => Service::SERVICE_TYPE_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_YES,
                    'representativeApproved' => Service::SURFCONEXT_APPROVED_YES,
                    'contractSigned' => null,
                    'isPrivacyQuestionsEnabled' => true,
                    'connectionStatus' => Service::CONNECTION_STATUS_ACTIVE,
                    'entities' => [
                        ['id' => 1, 'name' => 'entity-1', 'environment' => Entity::ENVIRONMENT_PRODUCTION],
                        ['id' => 2, 'name' => 'entity-2', 'environment' => Entity::ENVIRONMENT_TEST],
                        ['id' => 3, 'name' => 'entity-3', 'environment' => Entity::ENVIRONMENT_TEST],
                    ],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_YES,
                    'privacyQuestions' => true,
                ],
                'result' => '{
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "representative-approved": "success",
    "privacy-questions": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "intake-conducted": "service.overview.progress.tooltip.intake-conducted.success.html",
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.success.html",
    "representative-approved": "service.overview.progress.tooltip.representative-approved.success.html",
    "privacy-questions": "service.overview.progress.tooltip.privacy-questions.success.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.success.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 100
}
'
            ],
            'privacy-questions-disabled' => [
                'service' => [
                    'id' => 2,
                    'name' => 'service-2',
                    'type' => Service::SERVICE_TYPE_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_YES,
                    'representativeApproved' => Service::SURFCONEXT_APPROVED_YES,
                    'contractSigned' => null,
                    'isPrivacyQuestionsEnabled' => false,
                    'connectionStatus' => Service::CONNECTION_STATUS_ACTIVE,
                    'entities' => [],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_YES,
                    'privacyQuestions' => false,
                ],
                'result' => '{
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "representative-approved": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "intake-conducted": "service.overview.progress.tooltip.intake-conducted.success.html",
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.success.html",
    "representative-approved": "service.overview.progress.tooltip.representative-approved.success.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.success.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 100
}
'
            ],
            'institute-output-danger' => [
                'service' => [
                    'id' => 2,
                    'name' => 'service-2',
                    'type' => Service::SERVICE_TYPE_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_NO,
                    'representativeApproved' => Service::SURFCONEXT_APPROVED_NO,
                    'contractSigned' => null,
                    'isPrivacyQuestionsEnabled' => true,
                    'connectionStatus' => Service::CONNECTION_STATUS_NOT_REQUESTED,
                    'entities' => [],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_NO,
                    'privacyQuestions' => false,
                ],
                'result' => '{
  "states": {
    "intake-conducted": "info",
    "entity-on-test": "info",
    "representative-approved": "info",
    "privacy-questions": "info",
    "production-connection": "info"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "intake-conducted": "service.overview.progress.tooltip.intake-conducted.info.html",
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.info.html",
    "representative-approved": "service.overview.progress.tooltip.representative-approved.info.html",
    "privacy-questions": "service.overview.progress.tooltip.privacy-questions.info.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.info.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 0
}
'
            ],
            'none-institute-output-danger' => [
                'service' => [
                    'id' => 2,
                    'name' => 'service-2',
                    'type' => Service::SERVICE_TYPE_NON_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_NO,
                    'representativeApproved' => null,
                    'contractSigned' => Service::CONTRACT_SIGNED_NO,
                    'isPrivacyQuestionsEnabled' => true,
                    'connectionStatus' => Service::CONNECTION_STATUS_NOT_REQUESTED,
                    'entities' => [],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_NO,
                    'privacyQuestions' => false,
                ],
                'result' => '
                {
  "states": {
    "intake-conducted": "info",
    "entity-on-test": "info",
    "contract-signed": "info",
    "privacy-questions": "info",
    "production-connection": "info"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "intake-conducted": "service.overview.progress.tooltip.intake-conducted.info.html",
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.info.html",
    "contract-signed": "service.overview.progress.tooltip.contract-signed.info.html",
    "privacy-questions": "service.overview.progress.tooltip.privacy-questions.info.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.info.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 0
}

'
            ],
            'institute-output-info' => [
                'service' => [
                    'id' => 2,
                    'name' => 'service-2',
                    'type' => Service::SERVICE_TYPE_INSTITUTE,
                    'intakeStatus' => Service::INTAKE_STATUS_NOT_APPLICABLE,
                    'representativeApproved' => Service::SURFCONEXT_APPROVED_YES,
                    'contractSigned' => null,
                    'isPrivacyQuestionsEnabled' => true,
                    'connectionStatus' => Service::CONNECTION_STATUS_SURFCONEXT_INFORMED,
                    'entities' => [],
                ],
                'statusService' => [
                    'entityStatus' => Service::ENTITY_PUBLISHED_IN_PROGRESS,
                    'privacyQuestions' => false,
                ],
                'result' => '{
  "states": {
    "entity-on-test": "in-progress",
    "representative-approved": "success",
    "privacy-questions": "info",
    "production-connection": "in-progress"
  },
  "labels": {
    "intake-conducted": "service.overview.progress.label.intake-conducted",
    "entity-on-test": "service.overview.progress.label.entity-on-test",
    "representative-approved": "service.overview.progress.label.representative-approved",
    "contract-signed": "service.overview.progress.label.contract-signed",
    "privacy-questions": "service.overview.progress.label.privacy-questions",
    "production-connection": "service.overview.progress.label.production-connection"
  },
  "tooltips": {
    "entity-on-test": "service.overview.progress.tooltip.entity-on-test.in-progress.html",
    "representative-approved": "service.overview.progress.tooltip.representative-approved.success.html",
    "privacy-questions": "service.overview.progress.tooltip.privacy-questions.info.html",
    "production-connection": "service.overview.progress.tooltip.production-connection.in-progress.html"
  },
  "legend": {
    "info": {
      "label": "service.overview.legend.info",
      "color": "#d1d2d6"
    },
    "in-progress": {
      "label": "service.overview.legend.in-progress",
      "color": "#f6aa61"
    },
    "success": {
      "label": "service.overview.legend.success",
      "color": "#67a979"
    }
  },
  "percentage": 25
}
'
            ],
        ];
    }

    private function createStatusServiceMock($data)
    {
        $this->serviceStatusService->shouldReceive('getEntityStatus')
            ->andReturn($data['entityStatus']);

        $this->serviceStatusService->shouldReceive('hasPrivacyQuestions')
            ->andReturn($data['privacyQuestions']);
    }

    private function createServiceMock($data)
    {
        $this->service->shouldReceive('getId')
            ->andReturn($data['id']);

        $this->service->shouldReceive('getName')
            ->andReturn($data['name']);

        $this->service->shouldReceive('getServiceType')
            ->andReturn($data['type']);

        $this->service->shouldReceive('getIntakeStatus')
            ->andReturn($data['intakeStatus']);

        $this->service->shouldReceive('getSurfconextRepresentativeApproved')
            ->andReturn($data['representativeApproved']);

        $this->service->shouldReceive('getContractSigned')
            ->andReturn($data['contractSigned']);

        $this->service->shouldReceive('isPrivacyQuestionsEnabled')
            ->andReturn($data['isPrivacyQuestionsEnabled']);

        $this->service->shouldReceive('getConnectionStatus')
            ->andReturn($data['connectionStatus']);
    }

    private function createEntityMock($id, $name, $environment)
    {
        $entity = m::mock(Entity::class);

        $entity->shouldReceive('getId')
            ->andReturn($id);

        $entity->shouldReceive('getEntityId')
            ->andReturn($id);

        $entity->shouldReceive('getNameEn')
            ->andReturn($name);

        $entity->shouldReceive('getAdministrativeContact')
            ->andReturn(null);

        $entity->shouldReceive('getStatus')
            ->andReturn(true);

        $entity->shouldReceive('getEnvironment')
            ->andReturn($environment);

        return $entity;
    }
}
