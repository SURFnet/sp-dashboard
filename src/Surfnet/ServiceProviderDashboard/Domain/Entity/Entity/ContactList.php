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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

class ContactList
{
    private static $supportedContactTypes = [
        'technical',
        'administrative',
        'support',
    ];

    private $contacts = [];

    public static function fromApiResponse(array $metaDataFields)
    {
        // 1. Structure the flat keyed data into an associative array
        $contactsData = [];
        foreach ($metaDataFields as $fieldName => $value) {
            if (substr($fieldName, 0, 9) === 'contacts:') {
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

    public function add(Contact $contact)
    {
        $this->contacts[$contact->getType()] = $contact;
    }

    /**
     * @return Contact|null
     */
    public function findTechnicalContact()
    {
        if (isset($this->contacts['technical'])) {
            return $this->contacts['technical'];
        }
        return null;
    }

    /**
     * @return Contact|null
     */
    public function findAdministrativeContact()
    {
        if (isset($this->contacts['administrative'])) {
            return $this->contacts['administrative'];
        }
        return null;
    }

    /**
     * @return Contact|null
     */
    public function findSupportContact()
    {
        if (isset($this->contacts['support'])) {
            return $this->contacts['support'];
        }
        return null;
    }

    private function clear()
    {
        $this->contacts = [];
    }

    public function merge(?ContactList $contacts)
    {
        $this->clear();
        if ($contacts !== null) {
            if ($technical = $contacts->findTechnicalContact()) {
                $this->add($technical);
            }
            if ($support = $contacts->findSupportContact()) {
                $this->add($support);
            }
            if ($administrative = $contacts->findAdministrativeContact()) {
                $this->add($administrative);
            }
        }
    }
}
