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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class InviteDeeplinkExtension extends AbstractExtension
{
    public function __construct(private readonly string $inviteUrl)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('to_invite_deeplink', [$this, 'toInviteDeeplink'], ['is_safe' => ['html']]),
        ];
    }

    public function toInviteDeeplink(?int $inviteRoleId): string
    {
        return $this->inviteUrl . '/roles/' . $inviteRoleId;
    }
}
