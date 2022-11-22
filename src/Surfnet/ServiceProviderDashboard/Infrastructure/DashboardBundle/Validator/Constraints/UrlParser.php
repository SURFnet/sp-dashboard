<?php

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InvalidArgumentException;

class UrlParser
{
    const SCHEME_PATTERN = '#^(http|ftp)s?://#i';

    public function __construct(private string $url)
    {
    }

    public function parse(): array
    {
        if (preg_match(self::SCHEME_PATTERN, $this->url) === 0) {
            $this->url = 'https://' . preg_replace('#^//#', '', $this->url, 1);
        }

        $parts = parse_url($this->url);

        if ($parts === false) {
            throw new InvalidArgumentException(sprintf('Invalid url %s', $this->url));
        }

        return $parts;
    }
}
