<?php

/**
 * Copyright 2023 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class ManageEntityTest extends TestCase
{
    public function test_it_can_has_status_published_when_excluded_from_push()
    {
        $data = json_decode(
            file_get_contents(__DIR__ . '/fixture/manage_entity_saml20_excluded_from_push.json'),
            true
        );
        $manageEntity = ManageEntity::fromApiResponse($data);

        static::assertEquals('published', $manageEntity->getStatus());
        static::assertTrue($manageEntity->isExcludedFromPushSet());
    }

    public function test_it_updates_status()
    {
        $data = json_decode(
            file_get_contents(__DIR__.'/fixture/manage_entity_saml20_excluded_from_push.json'),
            true
        );
        $manageEntity = ManageEntity::fromApiResponse($data);

        static::assertTrue($manageEntity->isExcludedFromPushSet());

        $manageEntity->updateStatusByExcludeFromPush();

        static::assertEquals('requested', $manageEntity->getStatus());
        static::assertTrue($manageEntity->isExcludedFromPushSet());

        $manageEntity->updateStatus(Constants::STATE_PUBLISHED);

        static::assertEquals('published', $manageEntity->getStatus());
        static::assertTrue($manageEntity->isExcludedFromPushSet());
    }

    public function test_it_does_not_update_when_not_excluded_from_push()
    {
        $data = json_decode(
            file_get_contents(__DIR__ . '/fixture/manage_entity_saml20_no_excluded_from_push.json'),
            true
        );
        $manageEntity = ManageEntity::fromApiResponse($data);

        static::assertEquals('published', $manageEntity->getStatus());
        static::assertFalse($manageEntity->isExcludedFromPushSet());

        $manageEntity->updateStatusByExcludeFromPush();

        static::assertEquals('published', $manageEntity->getStatus());
        static::assertFalse($manageEntity->isExcludedFromPushSet());
    }
}
