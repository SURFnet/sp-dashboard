<?php

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Invite;

use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\InviteHttpClientUrlSanitizer;

class InviteHttpClientUrlSanitizerTest extends TestCase
{

    public function testHandlesBaseUriCorrectly(): void
    {
        $baseUri = InviteHttpClientUrlSanitizer::buildBaseUri('/api/external/v1', 'http://localhost');
        self::assertSame('http://localhost/api/external/v1/', $baseUri);
    }

    public function testEnsuresRelativePathCorrectly(): void
    {
        $relativePath = InviteHttpClientUrlSanitizer::ensureRelativePath('/internal/delete');
        self::assertSame('internal/delete', $relativePath);
    }

    public function testHandlesEmptyPathInBaseUri(): void
    {
        $baseUri = InviteHttpClientUrlSanitizer::buildBaseUri('', 'http://localhost');
        self::assertSame('http://localhost/', $baseUri);
    }

    public function testHandlesEmptyHostInBaseUri(): void
    {
        $baseUri = InviteHttpClientUrlSanitizer::buildBaseUri('/api/external/v1', '');
        self::assertSame('/api/external/v1/', $baseUri);
    }

    public function testHandlesEmptyPathInRelativePath(): void
    {
        $relativePath = InviteHttpClientUrlSanitizer::ensureRelativePath('');
        self::assertSame('', $relativePath);
    }

    public function testCombinesBaseUriAndRelativePathCorrectly(): void
    {
        $baseUri = InviteHttpClientUrlSanitizer::buildBaseUri('/api/external/v1', 'http://localhost');
        $relativePath = InviteHttpClientUrlSanitizer::ensureRelativePath('/internal/delete');
        $fullUri = $baseUri . $relativePath;
        self::assertSame('http://localhost/api/external/v1/internal/delete', $fullUri);
    }
}