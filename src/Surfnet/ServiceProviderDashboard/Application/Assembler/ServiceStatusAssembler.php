<?php

//declare(strict_types = 1);

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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Twig\WysiwygExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

class ServiceStatusAssembler
{
    /**
     * The different status types used
     */
    final public const SERVICE_STATE_INTAKE_CONDUCTED = 'intake-conducted';
    final public const SERVICE_STATE_ENTITY_ON_TEST = 'entity-on-test';
    final public const SERVICE_STATE_REPRESENTATIVE_APPROVED = 'representative-approved';
    final public const SERVICE_STATE_CONTRACT_SIGNED = 'contract-signed';
    final public const SERVICE_STATE_PRIVACY_QUESTIONS = 'privacy-questions';
    final public const SERVICE_STATE_PRODUCTION_CONNECTION = 'production-connection';

    /**
     * The possible states used in the mapping
     */
    final public const SERVICE_STATUS_SUCCESS = 'success';
    final public const SERVICE_STATUS_INFO = 'info';
    final public const SERVICE_STATUS_IN_PROGRESS = 'in-progress';

    /**
     * The mapping of the different states
     */
    private array $stateMapping = [
        self::SERVICE_STATE_INTAKE_CONDUCTED => [
            Service::INTAKE_STATUS_YES => self::SERVICE_STATUS_SUCCESS,
            Service::INTAKE_STATUS_NO => self::SERVICE_STATUS_INFO,
        ],
        self::SERVICE_STATE_ENTITY_ON_TEST => [
            Service::ENTITY_PUBLISHED_YES => self::SERVICE_STATUS_SUCCESS,
            Service::ENTITY_PUBLISHED_NO => self::SERVICE_STATUS_INFO,
            Service::ENTITY_PUBLISHED_IN_PROGRESS => self::SERVICE_STATUS_IN_PROGRESS,
        ],
        self::SERVICE_STATE_REPRESENTATIVE_APPROVED => [
            Service::SURFCONEXT_APPROVED_YES => self::SERVICE_STATUS_SUCCESS,
            Service::SURFCONEXT_APPROVED_NO => self::SERVICE_STATUS_INFO,
        ],
        self::SERVICE_STATE_CONTRACT_SIGNED => [
            Service::CONTRACT_SIGNED_YES => self::SERVICE_STATUS_SUCCESS,
            Service::CONTRACT_SIGNED_NO => self::SERVICE_STATUS_INFO,
        ],
        self::SERVICE_STATE_PRIVACY_QUESTIONS => [
            true => self::SERVICE_STATUS_SUCCESS,
            false => self::SERVICE_STATUS_INFO,
        ],
        self::SERVICE_STATE_PRODUCTION_CONNECTION => [
            Service::CONNECTION_STATUS_NOT_REQUESTED => self::SERVICE_STATUS_INFO,
            Service::CONNECTION_STATUS_REQUESTED => self::SERVICE_STATUS_IN_PROGRESS,
            Service::CONNECTION_STATUS_ACTIVE => self::SERVICE_STATUS_SUCCESS,
        ],
    ];

    /**
     * The mapping of the legend
     */
    private array $legend = [
        self::SERVICE_STATUS_SUCCESS => '#67a979',
        self::SERVICE_STATUS_IN_PROGRESS => '#f6aa61',
        self::SERVICE_STATUS_INFO => '#d1d2d6',
    ];

    private readonly \Surfnet\ServiceProviderDashboard\Application\Dto\ServiceStatusDto $serviceStatusDto;

    /**
     * ServiceStatusAssembler constructor.
     */
    public function __construct(
        Service $service,
        ServiceStatusService $serviceStatusService,
        private readonly TranslatorInterface $translator
    ) {
        $states = $this->getStates($service, $serviceStatusService);
        $mappedStates = $this->mapStates($states);
        $legend = $this->getLegend();
        $labels = $this->getLabels();
        $tooltips = $this->getTooltips($mappedStates);
        $percentage = $this->getPercentage($mappedStates);

        $this->serviceStatusDto = new ServiceStatusDto(
            $mappedStates,
            $labels,
            $tooltips,
            $legend,
            $percentage
        );
    }

    /**
     * @return ServiceStatusDto
     */
    public function getDto(): \Surfnet\ServiceProviderDashboard\Application\Dto\ServiceStatusDto
    {
        return $this->serviceStatusDto;
    }

    /**
     * @return string[]
     */
    private function states(): array
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
     * @return string[]
     */
    private function status(): array
    {
        return [
            self::SERVICE_STATUS_INFO,
            self::SERVICE_STATUS_IN_PROGRESS,
            self::SERVICE_STATUS_SUCCESS,
        ];
    }

    /**
     * @return array
     */
    private function getStates(Service $service, ServiceStatusService $serviceStatusService): array
    {
        $states = [];
        $type = $service->getServiceType();

        $intakeStatus = $service->getIntakeStatus();
        if ($intakeStatus != Service::INTAKE_STATUS_NOT_APPLICABLE) {
            $states[self::SERVICE_STATE_INTAKE_CONDUCTED] = $intakeStatus;
        }

        $states[self::SERVICE_STATE_ENTITY_ON_TEST] = $serviceStatusService->getEntityStatusOnTest($service);

        if ($type == Service::SERVICE_TYPE_NON_INSTITUTE) {
            $states[self::SERVICE_STATE_CONTRACT_SIGNED] = $service->getContractSigned();
        }

        if ($type == Service::SERVICE_TYPE_INSTITUTE) {
            $states[self::SERVICE_STATE_REPRESENTATIVE_APPROVED] = $service->getSurfconextRepresentativeApproved();
        }

        if ($service->isPrivacyQuestionsEnabled()) {
            $states[self::SERVICE_STATE_PRIVACY_QUESTIONS] = $serviceStatusService->hasPrivacyQuestions($service);
        }

        $states[self::SERVICE_STATE_PRODUCTION_CONNECTION] = $serviceStatusService->getConnectionStatus($service);

        return $states;
    }

    /**
     * @return array
     */
    private function mapStates(array $states): array
    {
        $result = [];
        foreach ($states as $name => $value) {
            if (is_null($value)) {
                continue;
            }
            $result[$name] = $this->stateMapping[$name][$value];
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getLegend(): array
    {
        $legend = [];
        foreach ($this->status() as $name) {
            $legend[$name] = [
                'label' => $this->translator->trans('service.overview.legend.'.$name),
                'color' => $this->legend[$name],
            ];
        }
        return $legend;
    }

    /**
     * @return mixed[]
     */
    private function getLabels(): array
    {
        $labels = [];
        foreach ($this->states() as $state) {
            $labels[$state] = $this->getSanitizedHtmlTranslation('service.overview.progress.label.' . $state);
        }
        return $labels;
    }

    /**
     * @return mixed[]
     */
    private function getTooltips(array $mappedStates): array
    {
        $tooltips = [];
        foreach ($mappedStates as $state => $status) {
            $tooltips[$state] = $this->getSanitizedHtmlTranslation('service.overview.progress.tooltip.' . $state . '.' . $status . '.html');
        }
        return $tooltips;
    }

    private function getPercentage(array $mappedStates): float
    {
        $total = 0;
        $done = 0;
        foreach ($mappedStates as $status) {
            if ($status == self::SERVICE_STATUS_SUCCESS) {
                $done++;
            }
            $total++;
        }
        return \round($done/$total*100);
    }

    private function getSanitizedHtmlTranslation(string $key)
    {
        $translated = $this->translator->trans($key);
        return WysiwygExtension::sanitizeWysiwyg($translated);
    }
}
