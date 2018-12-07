<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto;

use Webmozart\Assert\Assert;

class Logo
{
    private $url;
    private $width;
    private $height;

    public static function fromApiResponse(array $data)
    {
        $url = isset($data['logo:0:url']) ? $data['logo:0:url'] : '';
        $width = isset($data['logo:0:width']) ? (int) $data['logo:0:width'] : 0;
        $height = isset($data['logo:0:height']) ? (int) $data['logo:0:height'] : 0;

        Assert::string($url);
        Assert::integer($width);
        Assert::integer($height);

        return new self($url, $width, $height);
    }

    /**
     * @param string $url
     * @param int $width
     * @param int $height
     */
    private function __construct($url, $width, $height)
    {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }
}
