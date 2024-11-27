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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Invite;

class InviteHttpClientUrlSanitizer
{

    public static function buildBaseUri(string $path, string $host): string
    {
        if (trim($path) === '') {
            return self::ensureEndsWithSingleSlash($host);
        }

        $path = self::ensureRelativePath($path);
        $path = self::ensureEndsWithSingleSlash($path);

        $host = self::ensureEndsWithSingleSlash($host);

        return $host . $path;
    }

    /**
     * Ensure the relative path does not start with a slash.
     * The client basePath needs to end with a slash or the part after the host will get silently removed.
     * And the paths in the post/delete methods need to be relative or the path in the basePath will not get used.
     *
     * See https://symfony.com/doc/7.2/reference/configuration/framework.html#base-uri
     * @param string $path
     * @return string
     */
    public static function ensureRelativePath(string $path): string
    {
        return ltrim($path, '/');
    }

    private static function ensureEndsWithSingleSlash(string $string): string
    {
        return rtrim($string, '/') . '/';
    }
}
