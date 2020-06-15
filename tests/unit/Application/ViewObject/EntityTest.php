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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
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

        $this->assertEquals(
            $mayEdit,
            $entity->getActions()->allowEditAction(),
            sprintf($messageFormat, 'mayEdit', $testName)
        );
        $this->assertEquals(
            $mayDelete,
            $entity->getActions()->allowDeleteAction(),
            sprintf($messageFormat, 'mayDelete', $testName)
        );
        $this->assertEquals(
            $mayClone,
            $entity->getActions()->allowCloneAction(),
            sprintf($messageFormat, 'mayClone', $testName)
        );
        $this->assertEquals(
            $mayCopy,
            $entity->getActions()->allowCopyAction(),
            sprintf($messageFormat, 'mayCopy', $testName)
        );
        $this->assertEquals(
            $mayCopyToProduction,
            $entity->getActions()->allowCopyToProductionAction(),
            sprintf($messageFormat, 'mayCopyToProduction', $testName)
        );
    }

    public function provideEntityListActions()
    {
        // The expectations are in order: mayEdit, mayDelete, mayClone, mayCopy, mayCopyToProduction
        return [
            ['test draft', $this->buildEntity('draft', 'test', 'saml20'), true, true, false, false, false],
            ['test published', $this->buildEntity('published', 'test', 'saml20'), false, true, false, true, true],
            ['prod draft', $this->buildEntity('draft', 'production', 'saml20'), true, true, false, false, false],
            ['prod requested', $this->buildEntity('requested', 'production', 'saml20'), false, true, false, true, false],
            ['prod published', $this->buildEntity('published', 'production', 'saml20'), false, true, true, false, false],
            ['oidc draft', $this->buildEntity('draft', 'test', 'oidc'), false, false, false, false, false],
            ['oidc published', $this->buildEntity('published', 'test', 'oidc'), false, false, false, false, false],
            ['oidc prod draft', $this->buildEntity('draft', 'production', 'oidc'), false, false, false, false, false],
            ['oidc prod published', $this->buildEntity('requested', 'production', 'oidc'), false, false, false, false, false],
            ['oidc prod published', $this->buildEntity('published', 'production', 'oidc'), false, false, false, false, false],
        ];
    }

    /**
     * @param string $state
     * @param string $env
     * @param string $protocol
     * @return Entity
     */
    private function buildEntity($state, $env, $protocol)
    {
        $router = m::mock(RouterInterface::class);

        return new Entity(
            '116252ea-c19a-4842-9bb1-c8830cca780f',
            'https://example.com/saml/metadata',
            1,
            'example-entity',
            'John Doe',
            $state,
            $env,
            $protocol,
            $protocol === 'oidc' ? true : false,
            $router
        );
    }
}
