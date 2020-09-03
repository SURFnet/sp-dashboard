<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\Entity\Entity;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;

class ContactListTest extends TestCase
{
    /**
     * @dataProvider provideContactListTestData
     */
    public function test_it_can_merge_data(ContactList $list, ?ContactList $newData, ContactList $expectations)
    {
        $list->merge($newData);
        $actualSupport = $list->findSupportContact();
        $actualTechnical = $list->findTechnicalContact();
        $actualAdministrative = $list->findAdministrativeContact();

        if ($expectedSupport = $expectations->findSupportContact()) {
            self::assertInstanceOf(Contact::class, $actualSupport);
            self::assertEquals($expectedSupport->getEmail(), $actualSupport->getEmail());
        }
        if ($expectedTechnical = $expectations->findTechnicalContact()) {
            self::assertInstanceOf(Contact::class, $actualTechnical);
            self::assertEquals($expectedTechnical->getEmail(), $actualTechnical->getEmail());
        }
        if ($expectedAdministrative = $expectations->findAdministrativeContact()) {
            self::assertInstanceOf(Contact::class, $actualAdministrative);
            self::assertEquals($expectedAdministrative->getEmail(), $actualAdministrative->getEmail());
        }
        if (!$actualSupport && !$actualTechnical && !$actualAdministrative) {
            self::assertTrue(is_null($expectedSupport) && is_null($expectedTechnical) && is_null($expectedAdministrative));
        }
    }

    public function provideContactListTestData()
    {
        yield [
            $this->contactList(['technical', 'administrative', 'support']),
            $this->contactList(['technical', 'administrative', 'support']),
            $this->contactList(['technical', 'administrative', 'support'])
        ];
        yield [
            $this->contactList(['technical', 'administrative', 'support']),
            $this->contactList(['administrative', 'support']),
            $this->contactList(['administrative', 'support'])
        ];
        yield [
            $this->contactList(null),
            $this->contactList(['administrative', 'support']),
            $this->contactList(['administrative', 'support'])
        ];
        yield [
            $this->contactList(['technical', 'administrative', 'support']),
            $this->contactList(null),
            $this->contactList(null)
        ];
    }

    private function contactList($contacts = null)
    {
        $contactList = new ContactList();
        $baseData = [
            'givenName' => 'John',
            'surName' => 'Doe',
            'emailAddress' => 'john@example.com'
        ];

        if ($contacts) {
            foreach ($contacts as $contactType) {
                $contactList->add(Contact::from($baseData + ['contactType' => $contactType]));
            }
        }

        return $contactList;
    }
}
