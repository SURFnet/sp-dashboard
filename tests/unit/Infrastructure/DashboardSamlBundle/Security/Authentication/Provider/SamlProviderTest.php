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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Mailer;

use InvalidArgumentException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SAML2\Assertion;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\AssertionAdapter;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ContactRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Provider\SamlProvider;

class SamlProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var m\MockInterface|ContactRepository
     */
    private $contactRepo;
    /**
     * @var m\MockInterface|ServiceRepository
     */
    private $serviceRepo;
    /**
     * @var m\MockInterface|AttributeDictionary
     */
    private $attributeDictionary;
    /**
     * @var m\MockInterface|LoggerInterface
     */
    private $logger;

    protected function setUp(): void
    {
        $this->contactRepo = m::mock(ContactRepository::class);
        $this->serviceRepo = m::mock(ServiceRepository::class);
        $this->attributeDictionary = m::mock(AttributeDictionary::class);
        $this->logger = m::mock(LoggerInterface::class);

        parent::setUp();

    }

    public function test_administrator_teams_validation_accepts_valid_teams()
    {
        $provider = $this->buildProvider("'urn:collab:foo:team.foobar.com','urn:collab:foo:team.foobar.com','urn:collab:foo:team.foobar.com'");
        self::assertInstanceOf(
            SamlProvider::class,
            $provider
        );
    }

    public function test_surfconext_representative_teams_validation_accepts_valid_teams()
    {
        $provider = $this->buildProvider("'urn:collab:foo:team.foobar.com'", "'urn:collab:foo:team.foobar.com', 'urn:collab:foo:team.foobar2.com'");
        self::assertInstanceOf(
            SamlProvider::class,
            $provider
        );
    }

    public function test_administrator_teams_validation_rejects_invalid_teams()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All entries in the `administrator_teams` config parameter should be string.');
        $this->buildProvider(",345345,true,false,foo,bar");
    }

    public function test_authorization_user()
    {
        $provider = $this->buildProvider("'urn:collab:foo:team.foobar.com'", 'eduPersonEntitlement');

        $assertion = m::mock(Assertion::class);
        $adapter = m::mock(AssertionAdapter::class);
        $this->attributeDictionary->shouldReceive('translate')->andReturn($adapter);
        $adapter->shouldReceive('getNameID')->andReturn('does-not-exist-yet');
        $adapter->shouldReceive('getAttributeValue')->with('mail')->andReturn(['john@example.com']);
        $adapter->shouldReceive('getAttributeValue')->with('commonName')->andReturn(['John Doe']);
        $adapter->shouldReceive('getAttributeValue')->with('isMemberOf')->andReturn(['team-1', 'team-2']);
        $this->contactRepo->shouldReceive('findByNameId')->with('does-not-exist-yet')->andReturnNull();
        $service1 = m::mock(Service::class);
        $service2 = m::mock(Service::class);
        $this->serviceRepo->shouldReceive('findByTeamNames')->with(['team-1', 'team-2'])->andReturn([$service1, $service2]);
        $this->contactRepo->shouldReceive('save');
        $user = $provider->getUser($assertion);
        self::assertEquals($user->getRoles(), ['ROLE_USER']);
    }

    public function test_authorization_surfconext_representative()
    {
        $provider = $this->buildProvider("'urn:collab:foo:team.foobar.com'", 'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijk');

        $assertion = m::mock(Assertion::class);
        $adapter = m::mock(AssertionAdapter::class);
        $this->attributeDictionary->shouldReceive('translate')->andReturn($adapter);
        $adapter->shouldReceive('getNameID')->andReturn('does-not-exist-yet');
        $adapter->shouldReceive('getAttributeValue')->with('mail')->andReturn(['john@example.com']);
        $adapter->shouldReceive('getAttributeValue')->with('commonName')->andReturn(['John Doe']);
        $adapter->shouldReceive('getAttributeValue')->with('isMemberOf')->andReturn([]);
        $adapter->shouldReceive('getAttributeValue')->with('urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke', [])->andReturn(
            [
                'urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:ibuildings',
                'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijk',
            ]
        );
        $this->contactRepo->shouldReceive('findByNameId')->with('does-not-exist-yet')->andReturnNull();
        $this->serviceRepo->shouldReceive('findByTeamNames')->andReturnNull();
        $this->contactRepo->shouldReceive('save');
        $user = $provider->getUser($assertion);
        self::assertEquals($user->getRoles(), ['ROLE_SURFCONEXT_REPRESENTATIVE']);
    }

    public function test_authorization_surfconext_representative_and_user()
    {
        $provider = $this->buildProvider("'urn:collab:foo:team.foobar.com'", 'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijk');

        $assertion = m::mock(Assertion::class);
        $adapter = m::mock(AssertionAdapter::class);
        $this->attributeDictionary->shouldReceive('translate')->andReturn($adapter);
        $adapter->shouldReceive('getNameID')->andReturn('does-not-exist-yet');
        $adapter->shouldReceive('getAttributeValue')->with('mail')->andReturn(['john@example.com']);
        $adapter->shouldReceive('getAttributeValue')->with('commonName')->andReturn(['John Doe']);
        $adapter->shouldReceive('getAttributeValue')->with('isMemberOf')->andReturn(['team-1', 'team-2']);
        $adapter->shouldReceive('getAttributeValue')->with('urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke', [])->andReturn(
            [
                'urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:ibuildings',
                'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijk',
            ]
        );
        $service1 = m::mock(Service::class);
        $service2 = m::mock(Service::class);
        $this->serviceRepo->shouldReceive('findByTeamNames')->with(['team-1', 'team-2'])->andReturn([$service1, $service2]);
        $this->contactRepo->shouldReceive('findByNameId')->with('does-not-exist-yet')->andReturnNull();
        $this->serviceRepo->shouldReceive('findByTeamNames')->andReturnNull();
        $this->contactRepo->shouldReceive('save');
        $user = $provider->getUser($assertion);
        self::assertEquals($user->getRoles(), ['ROLE_SURFCONEXT_REPRESENTATIVE', 'ROLE_USER']);
    }

    private function buildProvider(string $administratorTeams, $surfConextResponsible = "defualt")
    {
        return new SamlProvider(
            $this->contactRepo,
            $this->serviceRepo,
            $this->attributeDictionary,
            $this->logger,
            'urn:mace:surfnet.nl:surfnet.nl:sab:role:SURFconext-verantwoordelijke',
            $surfConextResponsible,
            $administratorTeams,
        );
    }
}
