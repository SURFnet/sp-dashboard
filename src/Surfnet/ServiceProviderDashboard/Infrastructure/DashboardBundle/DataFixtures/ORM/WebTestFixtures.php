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
use Doctrine\Persistence\ObjectManager;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\DpaType;
use Symfony\Component\Uid\Uuid;

class WebTestFixtures extends Fixture
{
    public const TEAMNAME_SURF = 'urn:collab:group:dev.openconext.local:demo:openconext:org:surf.nl';
    public const TEAMNAME_IBUILDINGS = 'urn:collab:group:dev.openconext.local:demo:openconext:org:ibuildings.nl';
    public const TEAMNAME_ACME = 'demo:openconext:org:acme.com';

    public function load(ObjectManager $manager): void
    {
        $service = $this->createService('SURFnet', self::TEAMNAME_SURF);
        $service->setProductionEntitiesEnabled(false);
        $manager->persist($service);

        $service =  $this->createService('Ibuildings B.V.', self::TEAMNAME_IBUILDINGS);
        $service->setProductionEntitiesEnabled(true);
        $service->setPrivacyQuestionsEnabled(true);
        $service->setClientCredentialClientsEnabled(true);
        $manager->persist($service);

        // Service Ibuildings B.V. also has privacy questions
        $privacyQuestions = $this->createPrivacyQuestions($service);
        $manager->persist($privacyQuestions);

        // A third service is used to test the SURFConext responsible role which pivots on entities with institution_id manage entities
        $service = $this->createService('Acme Corporation', self::TEAMNAME_ACME);
        $service->setServiceType(Service::SERVICE_TYPE_INSTITUTE);
        $service->setInstitutionId('ACME Corporation');
        $manager->persist($service);

        $manager->flush();
    }

    
    private function createService(string $name, string $teamName): Service
    {
        $service = new Service();
        $service->setName($name);
        $service->setTeamName($teamName);
        $service->setGuid(Uuid::v4());
        $service->setInstitutionId(Uuid::v4());
        $service->setGuid(Uuid::v4());
        $service->setOrganizationNameEn($name);
        $service->setOrganizationNameNl($name);
        $service->setContractSigned('no');
        $service->setSurfconextRepresentativeApproved('yes');
        return $service;
    }

    private function createPrivacyQuestions(Service $service): PrivacyQuestions
    {
        $privacyQuestions = new PrivacyQuestions();
        $privacyQuestions->setService($service);
        $privacyQuestions->setWhatData('All your data are belong to us');
        $privacyQuestions->setDpaType(DpaType::DEFAULT);

        return $privacyQuestions;
    }
}
