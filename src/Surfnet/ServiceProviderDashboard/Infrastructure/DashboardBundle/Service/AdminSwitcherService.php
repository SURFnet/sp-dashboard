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
namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class AdminSwitcherService
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $supplierId
     *
     * @return AdminSwitcherService
     */
    public function setSelectedSupplier($supplierId)
    {
        $this->session->set('selected_supplier', $supplierId);

        return $this;
    }

    /**
     * @return string
     */
    public function getSelectedSupplier()
    {
        return $this->session->get('selected_supplier');
    }
}
