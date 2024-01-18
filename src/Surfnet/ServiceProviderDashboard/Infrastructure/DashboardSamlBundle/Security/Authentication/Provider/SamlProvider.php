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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Provider;

use BadMethodCallException;
use Psr\Log\LoggerInterface;
use SAML2\Assertion;
use Surfnet\SamlBundle\Exception\RuntimeException;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\AssertionAdapter;
use Surfnet\SamlBundle\Security\Authentication\Provider\SamlProviderInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ContactRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception\MissingSamlAttributeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception\UnknownServiceException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Webmozart\Assert\Assert;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamlProvider implements SamlProviderInterface, UserProviderInterface
{
    /**
     * @var string[]
     */
    private readonly array $administratorTeams;

    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly ServiceRepository $services,
        private readonly AttributeDictionary $attributeDictionary,
        private readonly LoggerInterface $logger,
        string $administratorTeams
    ) {
        $teams = explode(",", str_replace('\'', '', $administratorTeams));
        Assert::allStringNotEmpty(
            $teams,
            'All entries in the `administrator_teams` config parameter should be string.'
        );
        $this->administratorTeams = $teams;
    }

    public function getNameId(Assertion $assertion): string
    {
        return $this->attributeDictionary->translate($assertion)->getNameID();
    }

    public function getUser(Assertion $assertion): UserInterface
    {
        $translatedAssertion = $this->attributeDictionary->translate($assertion);

        $nameId = $this->getNameId($assertion);
        try {
            $email = $this->getSingleStringValue('mail', $translatedAssertion);
        } catch (MissingSamlAttributeException $e) {
            throw new BadCredentialsException($e->getMessage());
        }

        try {
            $commonName = $this->getSingleStringValue('commonName', $translatedAssertion);
        } catch (MissingSamlAttributeException) {
            $commonName = '';
        }

        // Default to the ROLE_USER role for services.
        $role = 'ROLE_USER';

        try {
            // An exception is thrown when isMemberOf is empty.
            $teamNames = (array)$translatedAssertion->getAttributeValue('isMemberOf');
        } catch (RuntimeException) {
            $teamNames = [];
        }

        if (array_intersect($this->administratorTeams, $teamNames) !== []) {
            $role = 'ROLE_ADMINISTRATOR';
        }

        $contact = $this->contacts->findByNameId($nameId);

        if ($contact === null) {
            $contact = new Contact($nameId, $email, $commonName);
        } elseif ($contact->getEmailAddress() !== $email || $contact->getDisplayName() !== $commonName) {
            $contact->setEmailAddress($email);
            $contact->setDisplayName($commonName);
        }

        if ($role === 'ROLE_USER') {
            $this->assignServicesToContact($contact, $teamNames);
            $this->contacts->save($contact);
        }
        $contact->assignRole($role);
        return new Identity($contact);
    }

    private function assignServicesToContact(Contact $contact, array $teamNames): void
    {
        $services = $this->services->findByTeamNames($teamNames);

        if (empty($services)) {
            $this->logger->warning(sprintf(
                'User is member of teams "%s" but no service with that team name found',
                implode(', ', $teamNames)
            ));

            throw new UnknownServiceException(
                $teamNames,
                'You do not have access to a service'
            );
        }

        foreach ($contact->getServices() as $existingService) {
            if (!$this->serviceListContainsService($services, $existingService)) {
                $contact->removeService($existingService);
            }
        }

        foreach ($services as $service) {
            if (!$contact->hasService($service)) {
                $contact->addService($service);
            }
        }
    }

    private function serviceListContainsService(array $list, Service $service): bool
    {
        foreach ($list as $serviceFromList) {
            if ($serviceFromList->getId() === $service->getId()) {
                return true;
            }
        }

        return false;
    }

    private function getSingleStringValue(string $attribute, AssertionAdapter $translatedAssertion): string
    {
        $values = $translatedAssertion->getAttributeValue($attribute);

        if (empty($values)) {
            $message = sprintf(
                'No value(s) found for attribute "%s"',
                $attribute
            );

            $this->logger->warning($message);

            throw new MissingSamlAttributeException(sprintf('Missing value for requested attribute "%s"', $attribute));
        }

        // see https://www.pivotaltracker.com/story/show/121296389
        if (count($values) > 1) {
            $this->logger->warning(sprintf(
                'Found "%d" values for attribute "%s", using first value',
                count($values),
                $attribute
            ));
        }

        $value = reset($values);

        if (!is_string($value)) {
            $message = sprintf(
                'First value of attribute "%s" must be a string, "%s" given',
                $attribute,
                get_debug_type($value)
            );

            $this->logger->warning($message);

            throw new MissingSamlAttributeException($message);
        }

        return $value;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === Identity::class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        throw new UserNotFoundException('Use `getUser` to load a user by username');
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new UserNotFoundException('Use `getUser` to load a user by username');
    }
}
