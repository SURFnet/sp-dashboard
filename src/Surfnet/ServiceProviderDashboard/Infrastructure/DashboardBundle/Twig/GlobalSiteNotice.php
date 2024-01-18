<?php

/**
 * Copyright 2021 SURFnet B.V.
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

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class GlobalSiteNotice extends AbstractExtension
{
    public function __construct(
        private readonly bool $shouldDisplayGlobalSiteNotice,
        private readonly string $allowedHtml,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('shouldDisplayGlobalSiteNotice', $this->shouldDisplayGlobalSiteNotice(...)),
            new TwigFunction('getGlobalSiteNotice', $this->getGlobalSiteNotice(...)),
            new TwigFunction('getAllowedHtmlForNotice', $this->getAllowedHtmlForNotice(...)),
        ];
    }

    public function shouldDisplayGlobalSiteNotice() : bool
    {
        return $this->shouldDisplayGlobalSiteNotice;
    }

    public function getGlobalSiteNotice(): string
    {
        return $this->translator->trans('site_notice.html');
    }

    public function getAllowedHtmlForNotice(): string
    {
        return $this->allowedHtml;
    }
}
