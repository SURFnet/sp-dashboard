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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\ViewObject;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Entity;
use Symfony\Component\Routing\RouterInterface;

class EntityTest extends MockeryTestCase
{
    /**
     * Test all the different entity states produce the correct user actions
     *
     * @dataProvider provideEntityListActions
     * @param string $testName
     * @param Entity $entity
     * @param bool $mayEdit
     * @param bool $mayClone
     * @param bool $mayCopy
     * @param bool $mayCopyToProduction
     * @param bool $mayDelete
     */
    public function test_entity_list_actions_are_determined_correctly(
        $testName,
        Entity $entity,
        $mayEdit,
        $mayDelete,
        $mayClone,
        $mayCopy,
        $mayCopyToProduction
    ) {
        $messageFormat = 'Unexpected outcome for the "%s" test in scenario "%s".';

        $this->assertEquals($mayEdit, $entity->allowEditAction(), sprintf($messageFormat, 'mayEdit', $testName));
        $this->assertEquals($mayDelete, $entity->allowDeleteAction(), sprintf($messageFormat, 'mayDelete', $testName));
        $this->assertEquals($mayClone, $entity->allowCloneAction(), sprintf($messageFormat, 'mayClone', $testName));
        $this->assertEquals($mayCopy, $entity->allowCopyAction(), sprintf($messageFormat, 'mayCopy', $testName));
        $this->assertEquals(
            $mayCopyToProduction,
            $entity->allowCopyToProductionAction(),
            sprintf($messageFormat, 'mayCopyToProduction', $testName)
        );
    }

    public function provideEntityListActions()
    {
        // The expectations are in order: mayEdit, mayDelete, mayClone, mayCopy, mayCopyToProduction
        return [
            ['test draft', $this->buildEntity('draft', 'test'), true, true, false, false, false],
            ['test published', $this->buildEntity('published', 'test'), false, false, false, true, true],
            ['prod draft', $this->buildEntity('draft', 'production'), true, true, false, false, false],
            ['prod requested', $this->buildEntity('requested', 'production'), false, false, false, true, false],
            ['prod published', $this->buildEntity('published', 'production'), false, false, true, false, false],
        ];
    }

    /**
     * @param string $state
     * @param string $env
     * @return Entity
     */
    private function buildEntity($state, $env)
    {
        $router = m::mock(RouterInterface::class);

        return new Entity(
            '116252ea-c19a-4842-9bb1-c8830cca780f',
            'https://example.com/saml/metadata',
            'example-entity',
            'John Doe',
            $state,
            $env,
            $router
        );
    }
}
