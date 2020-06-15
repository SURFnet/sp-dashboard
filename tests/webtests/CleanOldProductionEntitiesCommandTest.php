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

namespace Surfnet\ServiceProviderDashboard\Webtests;

use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CleanOldProductionEntitiesCommandTest extends WebTestCase
{

    const NOF_PUBLISHED_ON_PRODUCTION_ENTITIES = 20;
    const NOF_DRAFT_ON_PRODUCTION_ENTITIES = 2;

    public function setUp($loadFixtures = true)
    {
        parent::setUp();

        $this->loadFixtures();

        $this->addTestEntities(self::NOF_PUBLISHED_ON_PRODUCTION_ENTITIES, self::NOF_DRAFT_ON_PRODUCTION_ENTITIES);
    }

    public function testExecute()
    {
        $kernel = $this->client->getKernel();

        $application = new Application($kernel);

        $command = $application->find('sp-dashboard:clear-old-entities');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
        ));

        // test the result
        $output = $commandTester->getDisplay();
        $this->assertContains('Done', $output);

        // test the entities
        $ids = [];
        $allEntities = $this->getEntityRepository()->findAll();
        foreach ($allEntities as $entity) {
            $ids[] = $entity->getEntityId();
        }

        // assert remaining entities
        $this->assertSame([
            'SP1',
            'SP2',
            'b8e7cffd-0409-45c7-a37a-000000000000',
            'b8e7cffd-0409-45c7-a37a-000000000001',
        ], $ids);
    }

    /**
     * @param int $nofInvalid
     * @param int $nofValid
     */
    private function addTestEntities($nofInvalid, $nofValid)
    {
        $service = new Service();
        $service->setName('Console action test service');
        $service->setTeamName('team-x');
        $service->setGuid(Uuid::uuid4());

        $this->getServiceRepository()->save($service);

        for ($i = 0; $i < $nofInvalid; $i++) {
            $id = 'a8e7cffd-0409-45c7-a37a-' . str_pad($i, 12, '0', STR_PAD_LEFT);
            $entity = new Entity();
            $entity->setId($id);
            $entity->setService($service);
            $entity->setManageId($id);
            $entity->setEntityId($id);
            $entity->setStatus(Entity::STATE_PUBLISHED);
            $entity->setEnvironment(Entity::ENVIRONMENT_PRODUCTION);

            $this->getEntityRepository()->save($entity);
        }

        for ($i = 0; $i < $nofValid; $i++) {
            $id = 'b8e7cffd-0409-45c7-a37a-' . str_pad($i, 12, '0', STR_PAD_LEFT);
            $entity = new Entity();
            $entity->setId($id);
            $entity->setService($service);
            $entity->setManageId($id);
            $entity->setEntityId($id);
            $entity->setStatus(Entity::STATE_DRAFT);
            $entity->setEnvironment(Entity::ENVIRONMENT_PRODUCTION);

            $this->getEntityRepository()->save($entity);
        }
    }
}
