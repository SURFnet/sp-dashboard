<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Exception;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;
use function array_flip;
use function array_key_exists;

class ContactList implements Comparable
{
    // Contacts are indexed on an integer index that is set to the contact type
    // This allows us to reference the contact information in Manage without issue
    private static array $supportedContactTypes = [
        0 => 'administrative',
        1 => 'technical',
        2 => 'support',
    ];

    private $contacts = [];

    public static function fromApiResponse(array $metaDataFields): self
    {
        // 1. Structure the flat keyed data into an associative array
        $contactsData = [];
        foreach ($metaDataFields as $fieldName => $value) {
            if (str_starts_with($fieldName, 'contacts:')) {
                $fieldNameParts = explode(':', $fieldName);
                $contactsData[$fieldNameParts[1]][$fieldNameParts[2]] = $value;
            }
        }

        // 2. Build the Contact DTOs
        $list = new self();
        foreach ($contactsData as $contact) {
            if (array_key_exists('contactType', $contact) && in_array($contact['contactType'], self::$supportedContactTypes)) {
                $list->add(Contact::from($contact));
            }
        }

        return $list;
    }

    public function add(Contact $contact): void
    {
        $this->contacts[$this->getIndexByType($contact->getType())] = $contact;
    }

    /**
     * @return Contact|null
     */
    public function findTechnicalContact()
    {
        $index = $this->getIndexByType('technical');
        return $this->contacts[$index] ?? null;
    }

    /**
     * @return Contact|null
     */
    public function findAdministrativeContact()
    {
        $index = $this->getIndexByType('administrative');
        return $this->contacts[$index] ?? null;
    }

    /**
     * @return Contact|null
     */
    public function findSupportContact()
    {
        $index = $this->getIndexByType('support');
        return $this->contacts[$index] ?? null;
    }

    private function clear(): void
    {
        $this->contacts = [];
    }

    public function merge(ContactList $contacts): void
    {
        $this->clear();
        if ($contacts instanceof ContactList) {
            $technical = $contacts->findTechnicalContact();
            if ($technical !== null) {
                $this->add($technical);
            }
            $support = $contacts->findSupportContact();
            if ($support !== null) {
                $this->add($support);
            }
            $administrative = $contacts->findAdministrativeContact();
            if ($administrative !== null) {
                $this->add($administrative);
            }
        }
    }

    public function asArray(): array
    {
        $data = [];
        foreach ($this->contacts as $index => $contact) {
            $data[sprintf('metaDataFields.contacts:%d:contactType', $index)] = $contact->getType();
            $data[sprintf('metaDataFields.contacts:%d:givenName', $index)] = $contact->getGivenName();
            $data[sprintf('metaDataFields.contacts:%d:surName', $index)] = $contact->getSurName();
            $data[sprintf('metaDataFields.contacts:%d:emailAddress', $index)] = $contact->getEmail();
            $data[sprintf('metaDataFields.contacts:%d:telephoneNumber', $index)] = $contact->getPhone();
        }
        return $data;
    }

    private function getIndexByType(string $type): int
    {
        $types = array_flip(self::$supportedContactTypes);
        if (!array_key_exists($type, $types)) {
            throw new Exception(sprintf('This contact person type (%s) is not supported', $type));
        }
        return $types[$type];
    }
}
