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
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class WebTestFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $service = $this->createService('SURFnet', 'urn:collab:org:surf.nl');
        $service->setProductionEntitiesEnabled(false);
        $manager->persist($service);

        $service =  $this->createService('Ibuildings B.V.', 'urn:collab:org:ibuildings.nl');
        $service->setProductionEntitiesEnabled(true);
        $service->setPrivacyQuestionsEnabled(true);
        $manager->persist($service);

        // Service Ibuildings B.V. also has privacy questions
        $privacyQuestions = $this->createPrivacyQuestions($service);
        $manager->persist($privacyQuestions);

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
        $service->setInstitutionId(Uuid::uuid4());
        $service->setGuid(Uuid::uuid4());
        $service->setOrganizationDisplayNameEn($name);
        $service->setOrganizationDisplayNameNl($name);
        $service->setOrganizationNameEn($name);
        $service->setOrganizationNameNl($name);
        return $service;
    }

    private function createPrivacyQuestions(Service $service)
    {
        $privacyQuestions = new PrivacyQuestions();
        $privacyQuestions->setService($service);
        $privacyQuestions->setWhatData('All your data are belong to us');

        return $privacyQuestions;
    }
}
