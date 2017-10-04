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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service as ServiceEntity;
use Surfnet\ServiceProviderDashboard\Domain\Model\Contact as Contact;

class Service
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $contact;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param string $id
     * @param string $name
     * @param string $contact
     * @param string $environment
     */
    public function __construct($id, $name, $contact, $environment)
    {
        $this->id = $id;
        $this->name = $name;
        $this->contact = $contact;
        $this->environment = $environment;
    }

    public static function fromEntity(ServiceEntity $service)
    {
        $contact = $service->getAdministrativeContact();

        $formattedContact = '';

        if ($contact) {
            $formattedContact = self::formatContact($contact);
        }

        return new self(
            $service->getId(),
            $service->getNameEn(),
            $formattedContact,
            $service->getEnvironment()
        );
    }

    /**
     * @return string
     */
    private static function formatContact(Contact $contact)
    {
        return sprintf(
            '%s %s (%s)',
            $contact->getFirstName(),
            $contact->getLastName(),
            $contact->getEmail()
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
