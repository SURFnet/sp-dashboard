<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Application\ViewObject;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityActions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

class EntityActionsTest extends TestCase
{
    public function test_it_hides_idp_whitlist_option_for_oidcng_resource_server()
    {
        $actions = new EntityActions('manage-id', 1, Entity::STATE_PUBLISHED, Entity::ENVIRONMENT_TEST, Entity::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER);
        $this->assertFalse($actions->allowAclAction());
    }
}
