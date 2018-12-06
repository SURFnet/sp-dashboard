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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto;

class ContactList
{
    private $contacts = [];

    public function add(Contact $contact)
    {
        $this->contacts[$contact->getType()] = $contact;
    }

    public function findTechnicalContact()
    {
        if (isset($this->contacts['technical'])) {
            return $this->contacts['technical'];
        }
        return null;
    }

    public function findAdministrativeContact()
    {
        if (isset($this->contacts['administrative'])) {
            return $this->contacts['administrative'];
        }
        return null;
    }

    public function findSupportContact()
    {
        if (isset($this->contacts['support'])) {
            return $this->contacts['support'];
        }
        return null;
    }
}
