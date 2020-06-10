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
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SAML2\Assertion;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\AssertionAdapter;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ContactRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Provider\SamlProvider;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class SamlProviderTest extends TestCase
{
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

    protected function setUp()
    {
        $this->contactRepo = m::mock(ContactRepository::class);
        $this->serviceRepo = m::mock(ServiceRepository::class);
        $this->attributeDictionary = m::mock(AttributeDictionary::class);
        $this->logger = m::mock(LoggerInterface::class);

        parent::setUp();
    }

    /**
     * @dataProvider provideValidAdminTeams
     */
    public function test_administrator_teams_validation_accepts_valid_teams($validAdminTeams)
    {
        $this->assertInstanceOf(SamlProvider::class, $this->buildProvider($validAdminTeams));
    }

    /**
     * @dataProvider provideInvalidAdminTeams
     */
    public function test_administrator_teams_validation_rejects_invalid_teams($invalidAdminTeams)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All entries in the `administrator_teams` config parameter should be string.');
        $this->buildProvider($invalidAdminTeams);
    }


    public function test_happy_flow()
    {
        $provider  = $this->buildProvider(['urn:collab:foo:team.foobar.com']);

        $nameId = 'urn:collab:foo:team.foobar.com';
        $email = 'foo@team.foobar.com';

        $assertion = m::mock(Assertion::class);
        $token = m::mock(TokenInterface::class);
        $assertionAdapter = m::mock(AssertionAdapter::class);
        $token->assertion = $assertion;


        $this->attributeDictionary
            ->shouldReceive('translate')
            ->with($assertion)
            ->andReturn($assertionAdapter);

        $assertionAdapter
            ->shouldReceive('getNameID')
            ->andReturn($nameId);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('mail')
            ->andReturn([$email]);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('commonName')
            ->andReturn(['John Doe']);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('isMemberOf')
            ->andReturn(['urn:collab:foo:team.foobar.com']);

        $this->logger->shouldReceive('warning')
            ->with('No value(s) found for attribute "commonName"');

        $this->contactRepo
            ->shouldReceive('findByNameId')
            ->with($nameId)
            ->andReturn(null);

        /** @var SamlToken $result */
        $result = $provider->authenticate($token);

        /** @var Identity $user */
        $user = $result->getUser();

        /** @var Contact $contact */
        $contact = $user->getContact();

        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertSame('John Doe', $contact->getDisplayName());
        $this->assertSame($email, $contact->getEmailAddress());
    }


    public function test_happy_flow_with_empty_common_name()
    {
        $provider  = $this->buildProvider(['urn:collab:foo:team.foobar.com']);

        $nameId = 'urn:collab:foo:team.foobar.com';
        $email = 'foo@team.foobar.com';

        $assertion = m::mock(Assertion::class);
        $token = m::mock(TokenInterface::class);
        $assertionAdapter = m::mock(AssertionAdapter::class);
        $token->assertion = $assertion;


        $this->attributeDictionary
            ->shouldReceive('translate')
            ->with($assertion)
            ->andReturn($assertionAdapter);

        $assertionAdapter
            ->shouldReceive('getNameID')
            ->andReturn($nameId);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('mail')
            ->andReturn([$email]);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('commonName')
            ->andReturn([]);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('isMemberOf')
            ->andReturn(['urn:collab:foo:team.foobar.com']);

        $this->logger->shouldReceive('warning')
            ->with('No value(s) found for attribute "commonName"');

        $this->contactRepo
            ->shouldReceive('findByNameId')
            ->with($nameId)
            ->andReturn(null);

        /** @var SamlToken $result */
        $result = $provider->authenticate($token);

        /** @var Identity $user */
        $user = $result->getUser();

        /** @var Contact $contact */
        $contact = $user->getContact();

        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertSame('', $contact->getDisplayName());
        $this->assertSame($email, $contact->getEmailAddress());
    }

    public function test_saml_provider_should_reject_empty_mail()
    {
        $this->expectException(BadCredentialsException::class);
        $this->expectExceptionMessage('First value of attribute "mail" must be a string, "NULL" given');

        $provider = $this->buildProvider(['urn:collab:foo:team.foobar.com']);

        $nameId = 'urn:collab:foo:team.foobar.com';

        $assertion = m::mock(Assertion::class);
        $token = m::mock(TokenInterface::class);
        $assertionAdapter = m::mock(AssertionAdapter::class);
        $token->assertion = $assertion;

        $this->attributeDictionary
            ->shouldReceive('translate')
            ->with($assertion)
            ->andReturn($assertionAdapter);

        $assertionAdapter
            ->shouldReceive('getNameID')
            ->andReturn($nameId);

        $assertionAdapter
            ->shouldReceive('getAttributeValue')
            ->with('mail')
            ->andReturn([null]);

        $this->logger->shouldReceive('warning')
            ->with('First value of attribute "mail" must be a string, "NULL" given');

        $provider->authenticate($token);
    }

    public function provideValidAdminTeams()
    {
        return [
            [['urn:collab:foo:team.foobar.com']],
            [['urn:collab:foo:team.foobar.com', 'urn:collab:foo:team.foobar.com']],
        ];
    }

    public function provideInvalidAdminTeams()
    {
        return [
            [['']],
            [[345345]],
            [[true, false]],
            [[['foo', 'bar']]],
        ];
    }

    private function buildProvider($administratorTeams)
    {
        return new SamlProvider($this->contactRepo, $this->serviceRepo, $this->attributeDictionary, $this->logger, $administratorTeams);
    }
}
