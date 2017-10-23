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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

class WebTestFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(
            $this->createService('SURFnet', 'urn:collab:org:surf.nl')
                ->addEntity(
                    $this->createEntity('SP1')
                )
                ->addEntity(
                    $this->createEntity('SP2')
                )
        );

        $manager->persist(
            $this->createService('Ibuildings B.V.', 'urn:collab:org:ibuildings.nl')
        );

        $manager->flush();
    }

    /**
     * @param string $name
     * @param string $teamName
     *
     * @return Service
     */
    private function createService($name, $teamName)
    {
        $service = new Service();
        $service->setName($name);
        $service->setTeamName($teamName);
        $service->setGuid(Uuid::uuid4());

        return $service;
    }

    /**
     * @param string $name
     *
     * @return Entity
     */
    private function createEntity($name)
    {
        $entity = new Entity();
        $entity->setId(Uuid::uuid4());
        $entity->setNameEn($name);
        $entity->setEntityId($name);
        $entity->setEnvironment('connect');
        $entity->setAdministrativeContact(
            $this->createContact('John', 'Doe', 'jdoe@example.org')
        );

        return $entity;
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
