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

class ServiceStatusAssemblerTest extends MockeryTestCase
{
    /** @var Service|MockInterface */
    private $service;

    /** @var ServiceStatusService|MockInterface */
    private $serviceStatusService;

    /** @var RouterInterface|MockInterface */
    private $router;

    public function setUp()
    {
        $this->service = m::mock(Service::class);
        $this->serviceStatusService = m::mock(ServiceStatusService::class);
        $this->router = m::mock(RouterInterface::class);

        $this->router
            ->shouldReceive('generate')
            ->andReturnUsing(
                function ($route, $parameters) {
                    return $route.'?'.http_build_query($parameters);
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

        $serviceLink = 'service_edit?id='.$serviceData['id'];
        $entitiesList = $this->creatEntityList($serviceData['entities']);

        $assembler = new ServiceStatusAssembler(
            $this->service,
            $serviceLink,
            $this->serviceStatusService,
            $entitiesList,
            $this->getTestLabels(),
            $this->getTestTooltips()
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
  "name": "service-2",
  "link": "service_edit?id=2",
  "entities": [
    {
      "name": "entity-1",
      "environment": "production",
      "link": "entity_edit?id=1"
    }
  ],
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "representative-approved": "success",
    "privacy-questions": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
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
  "name": "service-1",
  "link": "service_edit?id=1",
  "entities": [
    {
      "name": "entity-1",
      "environment": "production",
      "link": "entity_edit?id=1"
    }
  ],
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "contract-signed": "success",
    "privacy-questions": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
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
  "name": "service-2",
  "link": "service_edit?id=2",
  "entities": [
    {
      "name": "entity-1",
      "environment": "production",
      "link": "entity_edit?id=1"
    },
    {
      "name": "entity-2",
      "environment": "test",
      "link": "entity_edit?id=2"
    },
    {
      "name": "entity-3",
      "environment": "test",
      "link": "entity_edit?id=3"
    }
  ],
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "representative-approved": "success",
    "privacy-questions": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
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
  "name": "service-2",
  "link": "service_edit?id=2",
  "entities": [],
  "states": {
    "intake-conducted": "success",
    "entity-on-test": "success",
    "representative-approved": "success",
    "production-connection": "success"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
}'
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
  "name": "service-2",
  "link": "service_edit?id=2",
  "entities": [],
  "states": {
    "intake-conducted": "danger",
    "entity-on-test": "danger",
    "representative-approved": "danger",
    "privacy-questions": "danger",
    "production-connection": "danger"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
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
                'result' => '{
  "name": "service-2",
  "link": "service_edit?id=2",
  "entities": [],
  "states": {
    "intake-conducted": "danger",
    "entity-on-test": "danger",
    "contract-signed": "danger",
    "privacy-questions": "danger",
    "production-connection": "danger"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
}'
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
  "name": "service-2",
  "link": "service_edit?id=2",
  "entities": [],
  "states": {
    "entity-on-test": "warning",
    "representative-approved": "success",
    "privacy-questions": "danger",
    "production-connection": "info"
  },
  "labels": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  },
  "tooltips": {
    "intake-conducted": "intake-conducted",
    "entity-on-test": "entity-on-test",
    "representative-approved": "representative-approved",
    "contract-signed": "contract-signed",
    "privacy-questions": "privacy-questions",
    "production-connection": "production-connection"
  }
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

    private function creatEntityList($data)
    {
        $entities = [];
        foreach ($data as $entityData) {
            $entity = $this->createEntityMock($entityData['id'], $entityData['name'], $entityData['environment']);
            $entities[] = EntityViewObject::fromEntity($entity, $this->router);
        }

        return new EntityList($entities);
    }

    /**
     * @return array
     */
    private function getTestLabels()
    {
        return [
            ServiceStatusAssembler::SERVICE_STATE_INTAKE_CONDUCTED => ServiceStatusAssembler::SERVICE_STATE_INTAKE_CONDUCTED,
            ServiceStatusAssembler::SERVICE_STATE_ENTITY_ON_TEST => ServiceStatusAssembler::SERVICE_STATE_ENTITY_ON_TEST,
            ServiceStatusAssembler::SERVICE_STATE_REPRESENTATIVE_APPROVED => ServiceStatusAssembler::SERVICE_STATE_REPRESENTATIVE_APPROVED,
            ServiceStatusAssembler::SERVICE_STATE_CONTRACT_SIGNED => ServiceStatusAssembler::SERVICE_STATE_CONTRACT_SIGNED,
            ServiceStatusAssembler::SERVICE_STATE_PRIVACY_QUESTIONS => ServiceStatusAssembler::SERVICE_STATE_PRIVACY_QUESTIONS,
            ServiceStatusAssembler::SERVICE_STATE_PRODUCTION_CONNECTION => ServiceStatusAssembler::SERVICE_STATE_PRODUCTION_CONNECTION,
        ];
    }

    /**
     * @return array
     */
    private function getTestTooltips()
    {
        return [
            ServiceStatusAssembler::SERVICE_STATE_INTAKE_CONDUCTED => ServiceStatusAssembler::SERVICE_STATE_INTAKE_CONDUCTED,
            ServiceStatusAssembler::SERVICE_STATE_ENTITY_ON_TEST => ServiceStatusAssembler::SERVICE_STATE_ENTITY_ON_TEST,
            ServiceStatusAssembler::SERVICE_STATE_REPRESENTATIVE_APPROVED => ServiceStatusAssembler::SERVICE_STATE_REPRESENTATIVE_APPROVED,
            ServiceStatusAssembler::SERVICE_STATE_CONTRACT_SIGNED => ServiceStatusAssembler::SERVICE_STATE_CONTRACT_SIGNED,
            ServiceStatusAssembler::SERVICE_STATE_PRIVACY_QUESTIONS => ServiceStatusAssembler::SERVICE_STATE_PRIVACY_QUESTIONS,
            ServiceStatusAssembler::SERVICE_STATE_PRODUCTION_CONNECTION => ServiceStatusAssembler::SERVICE_STATE_PRODUCTION_CONNECTION,
        ];
    }
}
