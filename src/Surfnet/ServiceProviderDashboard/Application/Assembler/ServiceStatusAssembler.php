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

namespace Surfnet\ServiceProviderDashboard\Application\Assembler;

use Surfnet\ServiceProviderDashboard\Application\Dto\ServiceStatusDto;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class ServiceStatusAssembler
{
    /**
     * The different status types used
     */
    const SERVICE_STATE_INTAKE_CONDUCTED = 'intake-conducted';
    const SERVICE_STATE_ENTITY_ON_TEST = 'entity-on-test';
    const SERVICE_STATE_REPRESENTATIVE_APPROVED = 'representative-approved';
    const SERVICE_STATE_CONTRACT_SIGNED = 'contract-signed';
    const SERVICE_STATE_PRIVACY_QUESTIONS = 'privacy-questions';
    const SERVICE_STATE_PRODUCTION_CONNECTION = 'production-connection';

    /**
     * The possible states used in the mapping
     */
    const SERVICE_STATUS_SUCCESS = 'success';
    const SERVICE_STATUS_INFO = 'info';
    const SERVICE_STATUS_WARNING = 'warning';
    const SERVICE_STATUS_DANGER = 'danger';

    /**
     * The mapping of the different states
     *
     * @var array
     */
    private $stateMapping = [
        self::SERVICE_STATE_INTAKE_CONDUCTED => [
            Service::INTAKE_STATUS_YES => self::SERVICE_STATUS_SUCCESS,
            Service::INTAKE_STATUS_NO => self::SERVICE_STATUS_DANGER,
        ],
        self::SERVICE_STATE_ENTITY_ON_TEST => [
            Service::ENTITY_PUBLISHED_YES => self::SERVICE_STATUS_SUCCESS,
            Service::ENTITY_PUBLISHED_NO => self::SERVICE_STATUS_DANGER,
            Service::ENTITY_PUBLISHED_IN_PROGRESS => self::SERVICE_STATUS_WARNING,
        ],
        self::SERVICE_STATE_REPRESENTATIVE_APPROVED => [
            Service::SURFCONEXT_APPROVED_YES => self::SERVICE_STATUS_SUCCESS,
            Service::SURFCONEXT_APPROVED_NO => self::SERVICE_STATUS_DANGER,
        ],
        self::SERVICE_STATE_CONTRACT_SIGNED => [
            Service::CONTRACT_SIGNED_YES => self::SERVICE_STATUS_SUCCESS,
            Service::CONTRACT_SIGNED_NO => self::SERVICE_STATUS_DANGER,
        ],
        self::SERVICE_STATE_PRIVACY_QUESTIONS => [
            true => self::SERVICE_STATUS_SUCCESS,
            false => self::SERVICE_STATUS_DANGER,
        ],
        self::SERVICE_STATE_PRODUCTION_CONNECTION => [
            Service::CONNECTION_STATUS_NOT_REQUESTED => self::SERVICE_STATUS_DANGER,
            Service::CONNECTION_STATUS_REQUESTED => self::SERVICE_STATUS_INFO,
            Service::CONNECTION_STATUS_SURFCONEXT_INFORMED => self::SERVICE_STATUS_INFO,
            Service::CONNECTION_STATUS_ACTIVE => self::SERVICE_STATUS_SUCCESS,
        ],
    ];

    /**
     * @var ServiceStatusDto
     */
    private $serviceStatusDto;

    /**
     * ServiceStatusAssembler constructor.
     * @param Service $service
     * @param string $serviceLink
     * @param ServiceStatusService $serviceStatusService
     * @param EntityList $entityList
     * @param string[] $labels
     * @param string[] $tooltips
     */
    public function __construct(
        Service $service,
        $serviceLink,
        ServiceStatusService $serviceStatusService,
        EntityList $entityList,
        array $labels,
        array $tooltips
    ) {
        $states = $this->getStates($service, $serviceStatusService);
        $mappedStates = $this->mapStates($states);

        $this->serviceStatusDto = new ServiceStatusDto(
            $service->getName(),
            $serviceLink,
            $entityList,
            $mappedStates,
            $labels,
            $tooltips
        );
    }

    /**
     * @return ServiceStatusDto
     */
    public function getDto()
    {
        return $this->serviceStatusDto;
    }

    /**
     * @return string[]
     */
    public static function states()
    {
        return [
            self::SERVICE_STATE_INTAKE_CONDUCTED,
            self::SERVICE_STATE_ENTITY_ON_TEST,
            self::SERVICE_STATE_REPRESENTATIVE_APPROVED,
            self::SERVICE_STATE_CONTRACT_SIGNED,
            self::SERVICE_STATE_PRIVACY_QUESTIONS,
            self::SERVICE_STATE_PRODUCTION_CONNECTION,
        ];
    }

    /**
     * @param Service $service
     * @param ServiceStatusService $serviceStatusService
     * @return array
     */
    private function getStates(Service $service, ServiceStatusService $serviceStatusService)
    {
        $states = [];
        $type = $service->getServiceType();

        $intakeStatus = $service->getIntakeStatus();
        if ($intakeStatus != Service::INTAKE_STATUS_NOT_APPLICABLE) {
            $states[self::SERVICE_STATE_INTAKE_CONDUCTED] = $intakeStatus;
        }

        $states[self::SERVICE_STATE_ENTITY_ON_TEST] = $serviceStatusService->getEntityStatus($service);

        if ($type == Service::SERVICE_TYPE_NON_INSTITUTE) {
            $states[self::SERVICE_STATE_CONTRACT_SIGNED] = $service->getContractSigned();
        }

        if ($type == Service::SERVICE_TYPE_INSTITUTE) {
            $states[self::SERVICE_STATE_REPRESENTATIVE_APPROVED] = $service->getSurfconextRepresentativeApproved();
        }

        if ($service->isPrivacyQuestionsEnabled()) {
            $states[self::SERVICE_STATE_PRIVACY_QUESTIONS] = $serviceStatusService->hasPrivacyQuestions($service);
        }

        $states[self::SERVICE_STATE_PRODUCTION_CONNECTION] = $service->getConnectionStatus();

        return $states;
    }

    /**
     * @param array $states
     * @return array
     */
    private function mapStates($states)
    {
        $result = [];
        foreach ($states as $name => $value) {
            $result[$name] = $this->stateMapping[$name][$value];
        }
        return $result;
    }
}
