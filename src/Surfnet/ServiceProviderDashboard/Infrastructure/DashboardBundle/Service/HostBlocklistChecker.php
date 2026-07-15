<?php

declare(strict_types = 1);

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use Symfony\Component\HttpFoundation\IpUtils;

class HostBlocklistChecker implements HostBlocklistCheckerInterface
{
    /**
     * @param string[] $blockedIpRanges CIDR notation, e.g. '10.0.0.0/8'
     */
    public function __construct(
        private readonly array $blockedIpRanges = [],
    ) {
    }

    public function isBlocked(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host === null || $host === false || $host === '') {
            return true;
        }

        return $this->isIpBlocked($this->resolve($host));
    }

    public function isIpBlocked(?string $ip): bool
    {
        if ($ip === null) {
            return true;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        return IpUtils::checkIp($ip, $this->blockedIpRanges);
    }

    public function resolve(string $host): ?string
    {
        $host = trim($host, '[]');

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $host;
        }

        $ip = gethostbyname($host);

        return $ip !== $host ? $ip : null;
    }
}
