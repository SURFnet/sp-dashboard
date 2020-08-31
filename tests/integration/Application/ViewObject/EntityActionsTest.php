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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;

class EntityActionsTest extends TestCase
{
    public function test_it_hides_idp_whitlist_option_for_oidcng_resource_server()
    {
        $actions = new EntityActions('manage-id', 1, Constants::STATE_PUBLISHED, Constants::ENVIRONMENT_TEST, Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER, false);
        $this->assertFalse($actions->allowAclAction());
    }

    /**
     * @param bool $expectation
     * @param string $protocol
     * @param string $publicationStatus
     * @param string $description
     *
     * @dataProvider resetClientOptions
     */
    public function test_oidc_entities_can_reset_client_secret($expectation, $protocol, $publicationStatus, $description)
    {
        $actions = new EntityActions('manage-id', 1, $publicationStatus, Constants::ENVIRONMENT_TEST, $protocol, false);

        $this->assertEquals($expectation, $actions->allowSecretResetAction(), $description);
    }

    public static function resetClientOptions()
    {
        return [
            [false, Constants::TYPE_OPENID_CONNECT, Constants::STATE_PUBLISHED, 'Published OIDC entity should have reset option'],
            [true, Constants::TYPE_OPENID_CONNECT_TNG, Constants::STATE_PUBLISHED, 'Published OIDC TNG entity should have reset option'],
            [true, Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER, Constants::STATE_PUBLISHED, 'Published OIDC Resource Server TNG entity should have reset option'],

            [false, Constants::TYPE_OPENID_CONNECT, Constants::STATE_PUBLICATION_REQUESTED, 'Request for publication OIDC entity should have reset option'],
            [true, Constants::TYPE_OPENID_CONNECT_TNG, Constants::STATE_PUBLICATION_REQUESTED, 'Request for publication OIDC TNG entity should have reset option'],
            [true, Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER, Constants::STATE_PUBLISHED, 'Request for publication OIDC Resource Server TNG entity should have reset option'],

            [false, Constants::TYPE_SAML, Constants::STATE_PUBLISHED, 'SAML entities do not perform client resets'],

            [false, Constants::TYPE_OPENID_CONNECT, Constants::STATE_DRAFT, 'Draft OIDC entities do not perform client resets'],
            [false, Constants::TYPE_OPENID_CONNECT, Constants::STATE_REMOVAL_REQUESTED, 'Removed (requested) entities do not perform client resets'],
        ];
    }

    public function test_read_only_restricts_cud_actions()
    {
        $actions = new EntityActions('manage-id', 1, Constants::STATE_DRAFT, Constants::ENVIRONMENT_TEST, Constants::TYPE_OPENID_CONNECT, true);
        $this->assertFalse($actions->allowEditAction());
        $this->assertFalse($actions->allowDeleteAction());
        $this->assertFalse($actions->allowAclAction());
        $this->assertFalse($actions->allowCopyAction());
        $this->assertFalse($actions->allowCopyToProductionAction());
        $this->assertFalse($actions->allowSecretResetAction());
    }
}
