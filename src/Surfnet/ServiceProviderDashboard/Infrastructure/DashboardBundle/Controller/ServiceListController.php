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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Service\SamlServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\SupplierService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ServiceListController extends Controller
{
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Application\Service\SamlServiceService
     */
    private $samlServiceService;

    /**
     * @var SupplierService
     */
    private $supplierService;

    /**
     * @param AuthorizationService $authorizationService
     * @param SamlServiceService $samlServiceService
     * @param SupplierService $supplierService
     */
    public function __construct(
        AuthorizationService $authorizationService,
        SamlServiceService $samlServiceService,
        SupplierService $supplierService
    ) {
        $this->authorizationService = $authorizationService;
        $this->samlServiceService = $samlServiceService;
        $this->supplierService = $supplierService;
    }

    /**
     * @Method("GET")
     * @Route("/", name="service_list")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function listAction()
    {
        $supplierOptions = $this->supplierService->getSupplierNames();

        if (empty($supplierOptions)) {
            return $this->redirectToRoute('supplier_add');
        }

        $selectedSupplierId = $this->authorizationService->getActiveSupplierId();

        return [
            'no_supplier_selected' => empty($selectedSupplierId),
            'service_list' => $this->samlServiceService->getServiceListForSupplier($selectedSupplierId),
        ];
    }
}
