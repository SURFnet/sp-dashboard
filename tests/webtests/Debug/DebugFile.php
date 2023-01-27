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

namespace Surfnet\ServiceProviderDashboard\Webtests\Debug;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\DomCrawler\Crawler;

class DebugFile
{
    #[NoReturn]
    public static function dump(array $var, string $filename): void
    {
        ob_start();
        var_dump($var);
        $data = ob_get_clean();
        $fp = fopen($filename, 'w');
        fwrite($fp, $data);
        fclose($fp);
    }

    #[NoReturn]
    public static function dumpAndDie(array $var, $filename): void
    {
        self::dump($var, $filename);
        die;
    }

    #[NoReturn]
    public static function dumpHtml(string $data, $filename): void
    {
        $fp = fopen($filename, 'w');
        fwrite($fp, $data);
        fclose($fp);
    }

    #[NoReturn]
    public static function dumpHtmlAndDie(string $data, $filename): void
    {
        self::dumpHtml($data, $filename);
        die;
    }

    #[NoReturn]
    public static function dumpCrawlerAndDie(Crawler $crawler, string $filename)
    {
        ob_start();
        $crawler->html();
        $data = ob_get_clean();
        $fp = fopen($filename, 'w');
        fwrite($fp, $data);
        fclose($fp);
        die;
    }
}