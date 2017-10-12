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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ContactRepository;

class ContactService
{
    private $contacts;

    public function __construct(ContactRepository $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @param string $nameId
     *
     * @return Contact|null
     */
    public function findByNameId($nameId)
    {
        return $this->contacts->findByNameId($nameId);
    }

    public function createContact(Contact $contact)
    {
        $this->contacts->save($contact);
    }

    public function updateContact(Contact $contact)
    {
        $this->contacts->save($contact);
    }
}
