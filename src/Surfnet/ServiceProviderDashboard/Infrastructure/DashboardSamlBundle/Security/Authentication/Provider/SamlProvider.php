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

use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\Exception\RuntimeException;
use Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary;
use Surfnet\SamlBundle\SAML2\Response\AssertionAdapter;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ContactRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception\MissingSamlAttributeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Exception\UnknownServiceException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Webmozart\Assert\Assert;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamlProvider implements AuthenticationProviderInterface
{
    /**
     * @var \Surfnet\ServiceProviderDashboard\Domain\Repository\ContactRepository
     */
    private $contacts;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository
     */
    private $services;

    /**
     * @var \Surfnet\SamlBundle\SAML2\Attribute\AttributeDictionary
     */
    private $attributeDictionary;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $administratorTeams;

    public function __construct(
        ContactRepository $contacts,
        ServiceRepository $services,
        AttributeDictionary $attributeDictionary,
        LoggerInterface $logger,
        array $administratorTeams
    ) {
        $this->contacts = $contacts;
        $this->services = $services;
        $this->attributeDictionary = $attributeDictionary;
        $this->logger = $logger;
        Assert::allStringNotEmpty(
            $administratorTeams,
            'All entries in the `administrator_teams` config parameter should be string.'
        );
        $this->administratorTeams = $administratorTeams;
    }

    /**
     * @param  SamlToken|TokenInterface $token
     *
     * @return TokenInterface
     */
    public function authenticate(TokenInterface $token)
    {
        $translatedAssertion = $this->attributeDictionary->translate($token->assertion);

        $nameId = $translatedAssertion->getNameID();
        try {
            $email = $this->getSingleStringValue('mail', $translatedAssertion);
        } catch (MissingSamlAttributeException $e) {
            throw new BadCredentialsException($e->getMessage());
        }

        try {
            $commonName = $this->getSingleStringValue('commonName', $translatedAssertion);
        } catch (MissingSamlAttributeException $e) {
            $commonName = '';
        }

        // Default to the ROLE_USER role for services.
        $role = 'ROLE_USER';

        try {
            // An exception is thrown when isMemberOf is empty.
            $teamNames = (array)$translatedAssertion->getAttributeValue('isMemberOf');
        } catch (RuntimeException $e) {
            $teamNames = [];
        }

        if (!empty(array_intersect($this->administratorTeams, $teamNames))) {
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

        $authenticatedToken = new SamlToken([$role]);
        $authenticatedToken->setUser(
            new Identity($contact)
        );

        return $authenticatedToken;
    }

    /**
     * @param Contact $contact
     * @param array $teamNames
     */
    private function assignServicesToContact(Contact $contact, array $teamNames)
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

    /**
     * @param array<Service> $list
     * @param Service $Service
     * @return bool
     */
    private function serviceListContainsService(array $list, Service $service)
    {
        foreach ($list as $serviceFromList) {
            if ($serviceFromList->getId() === $service->getId()) {
                return true;
            }
        }

        return false;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SamlToken;
    }

    /**
     * @param string           $attribute
     * @param AssertionAdapter $translatedAssertion
     * @return string
     */
    private function getSingleStringValue($attribute, AssertionAdapter $translatedAssertion)
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
                is_object($value) ? get_class($value) : gettype($value)
            );

            $this->logger->warning($message);

            throw new MissingSamlAttributeException($message);
        }

        return $value;
    }
}
