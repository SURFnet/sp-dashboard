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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoInvalidTypeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoNotFoundException;

interface LogoValidationHelperInterface
{
    /**
     * Valid type: PNG
     */
    public const IMAGE_TYPE_PNG = 'image/png';

    /**
     * Valid type: GIF
     */
    public const IMAGE_TYPE_GIF = 'image/gif';

    /**
     * Validates the logo, throws an exception if validation failed.
     *
     * @param  $url
     * @throws LogoInvalidTypeException
     * @throws LogoNotFoundException
     */
    public function validateLogo($url);
}
