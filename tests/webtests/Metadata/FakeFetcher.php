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

namespace Surfnet\ServiceProviderDashboard\Webtests\Metadata;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Metadata\FetcherInterface;

class FakeFetcher implements FetcherInterface
{
    public function fetch($url)
    {
        switch ($url) {
            case 'https://engine.surfconext.nl/authentication/sp/metadata':
                return file_get_contents(__DIR__ . '/../fixtures/metadata/valid_metadata.xml');
                break;
            case 'https://engine.surfconext.nl/authentication/sp/metadata-valid-incomplete':
                return file_get_contents(__DIR__ . '/../fixtures/metadata/valid_metadata_incomplete.xml');
                break;
        }
    }
}
