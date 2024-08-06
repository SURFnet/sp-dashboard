<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\DashboardBundle\Service;


use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Service\ContractualBaseService;

class ContractualBaseServiceTest extends TestCase
{
    private ContractualBaseService $service;

    protected function setUp(): void
    {
        $this->service = new ContractualBaseService();
    }

    /**
     * @dataProvider contractualBaseDataProvider
     */
    public function testWriteContractualBase(
        string $environment,
        string $protocol,
        string $serviceType,
        ?string $expectedContractualBase
    ): void {
        $entity = $this->createMockEntity($environment, $protocol, $serviceType);

        $this->service->writeContractualBase($entity);

        $this->assertEquals($expectedContractualBase, $entity->getMetaData()->getCoin()->getContractualBase());
    }

    public function contractualBaseDataProvider(): array
    {
        return [
            'Production SAML Institute' => [
                Constants::ENVIRONMENT_PRODUCTION,
                Constants::TYPE_SAML,
                Constants::SERVICE_TYPE_INSTITUTE,
                Constants::CONTRACTUAL_BASE_IX,
            ],
            'Production OIDC Institute' => [
                Constants::ENVIRONMENT_PRODUCTION,
                Constants::TYPE_OPENID_CONNECT_TNG,
                Constants::SERVICE_TYPE_INSTITUTE,
                Constants::CONTRACTUAL_BASE_IX,
            ],
            'Production SAML Non-Institute' => [
                Constants::ENVIRONMENT_PRODUCTION,
                Constants::TYPE_SAML,
                Constants::SERVICE_TYPE_NON_INSTITUTE,
                Constants::CONTRACTUAL_BASE_AO,
            ],
            'Production OIDC Non-Institute' => [
                Constants::ENVIRONMENT_PRODUCTION,
                Constants::TYPE_OPENID_CONNECT_TNG,
                Constants::SERVICE_TYPE_NON_INSTITUTE,
                Constants::CONTRACTUAL_BASE_AO,
            ],
            'Test Environment' => [
                Constants::ENVIRONMENT_TEST,
                Constants::TYPE_SAML,
                Constants::SERVICE_TYPE_INSTITUTE,
                null,
            ],
            'Unsupported Protocol' => [
                Constants::ENVIRONMENT_PRODUCTION,
                'unsupported_protocol',
                Constants::SERVICE_TYPE_INSTITUTE,
                null,
            ],
            'Unsupported Service Type' => [
                Constants::ENVIRONMENT_PRODUCTION,
                Constants::TYPE_SAML,
                'unsupported_service_type',
                null,
            ],
        ];
    }

    private function createMockEntity(string $environment, string $protocolValue, string $serviceType): ManageEntity
    {
        $metadata = $this->createMock(MetaData::class);
        // Use an acutal Coin instance, as the value for the contractual base is overwritten in the service
        $coin = new Coin(null, null, null, null, null, null, null, null, null);
        $service = $this->createMock(Service::class);
        $protocol = $this->createMock(Protocol::class);
        $protocol->method('getProtocol')->willReturn($protocolValue);

        $metadata->method('getCoin')->willReturn($coin);
        $service->method('getServiceType')->willReturn($serviceType);

        $entity = $this->createMock(ManageEntity::class);
        $entity->method('getEnvironment')->willReturn($environment);
        $entity->method('getProtocol')->willReturn($protocol);
        $entity->method('getMetaData')->willReturn($metadata);
        $entity->method('getService')->willReturn($service);

        return $entity;
    }
}
