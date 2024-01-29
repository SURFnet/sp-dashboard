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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;
use Webmozart\Assert\Assert;

class Logo implements Comparable
{
    public static function fromApiResponse(array $data): self
    {
        $url = $data['logo:0:url'] ?? '';
        $width = isset($data['logo:0:width']) ? (int) $data['logo:0:width'] : 0;
        $height = isset($data['logo:0:height']) ? (int) $data['logo:0:height'] : 0;

        Assert::string($url);
        Assert::integer($width);
        Assert::integer($height);

        return new self($url, $width, $height);
    }

    public function __construct(
        private ?string $url,
        private ?int $width,
        private ?int $height,
    ) {
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function merge(Logo $logo): void
    {
        // Overwrite the current data with that from the new logo
        $this->url = is_null($logo->getUrl()) ? null : $logo->getUrl();
        $this->width = is_null($logo->getWidth()) ? null : $logo->getWidth();
        $this->height = is_null($logo->getHeight()) ? null : $logo->getHeight();
    }

    public function asArray(): array
    {
        return [
            'metaDataFields.logo:0:url' => $this->getUrl(),
        ];
    }
}
