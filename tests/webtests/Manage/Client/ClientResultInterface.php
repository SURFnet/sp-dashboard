<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use function file_get_contents;
use function json_decode;
use function sprintf;
use function str_replace;

interface ClientResultInterface
{
    public function getEntityResult(): array;

    public function getSearchResult(): array;

    public function getId(): string;

    public function getEntityId(): string;

    public static function decode($data): self;

    public function encode(): array;
}
