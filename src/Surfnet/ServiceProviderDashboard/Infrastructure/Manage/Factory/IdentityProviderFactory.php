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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory;

use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;

class IdentityProviderFactory
{

    /**
     * @param  $manageResult
     */
    public static function fromManageResult(array $manageResult): IdentityProvider
    {
        return new IdentityProvider(
            $manageResult['_id'],
            $manageResult['data']['entityid'],
            $manageResult['data']['metaDataFields']['name:nl'],
            $manageResult['data']['metaDataFields']['name:en']
        );
    }
}
