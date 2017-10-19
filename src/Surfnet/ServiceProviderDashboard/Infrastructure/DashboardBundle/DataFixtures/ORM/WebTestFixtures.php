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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

class WebTestFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(
            $this->createSupplier('SURFnet', 'urn:collab:org:surf.nl')
                ->addService(
                    $this->createService('SP1')
                )
                ->addService(
                    $this->createService('SP2')
                )
        );

        $manager->persist(
            $this->createSupplier('Ibuildings B.V.', 'urn:collab:org:ibuildings.nl')
        );

        $manager->flush();
    }

    /**
     * @param string $name
     * @param string $teamName
     *
     * @return Supplier
     */
    private function createSupplier($name, $teamName)
    {
        $supplier = new Supplier();
        $supplier->setName($name);
        $supplier->setTeamName($teamName);
        $supplier->setGuid(Uuid::uuid4());

        return $supplier;
    }

    /**
     * @param string $name
     *
     * @return Service
     */
    private function createService($name)
    {
        $service = new Service();
        $service->setId(Uuid::uuid4());
        $service->setNameEn($name);
        $service->setEntityId($name);
        $service->setEnvironment('connect');
        $service->setAdministrativeContact(
            $this->createContact('John', 'Doe', 'jdoe@example.org')
        );

        return $service;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @return Contact
     */
    private function createContact($firstName, $lastName, $email)
    {
        $contact = new Contact();
        $contact->setFirstName($firstName);
        $contact->setLastName($lastName);
        $contact->setEmail($email);

        return $contact;
    }
}
