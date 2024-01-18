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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Twig;

use HTMLPurifier;
use HTMLPurifier_HTML5Config;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class WysiwygExtension extends AbstractExtension
{
    private static ?\HTMLPurifier $purifier = null;

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'wysiwyg',
                $this->sanitize(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function sanitize(string $raw): string
    {
        return self::sanitizeWysiwyg($raw);
    }

    public static function sanitizeWysiwyg(string $raw): string
    {
        self::initialise();
        return self::$purifier->purify($raw);
    }

    private static function initialise(): void
    {
        if (!is_null(self::$purifier)) {
            return;
        }

        $config = HTMLPurifier_HTML5Config::createDefault();

        $config->set('HTML.Doctype', 'HTML5');
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.AllowedElements', 'p,em,strong,span,h1,h2,h3,h4,h5,h6,ul,ol,li,a,sup,sub,code,blockquote,br,i');
        $config->set('HTML.AllowedAttributes', 'a.target,a.href,p.style,span.style,i.class');
        $config->set('CSS.AllowedProperties', 'text-decoration,text-align');
        $config->set('Attr.AllowedFrameTargets', '_blank,_self,_parent,_top');
        $config->set('Cache.DefinitionImpl', null);

        self::$purifier = new HTMLPurifier($config);
    }
}
