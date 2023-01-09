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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Handler;

use SAML2\Assertion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface AuthenticationHandler
{
    /**
     * Checks if it can process the event and if so does so. Also determines if there
     * is a next handler to be called if it cannot process the event itself.
     *
     * @param Request $request
     * @return Response
     */
    public function process(Request $request): Assertion;

    /**
     * Allows setting the optional next handler
     *
     * @param AuthenticationHandler $handler
     * @return void
     */
//    public function setNext(AuthenticationHandler $handler);
}
