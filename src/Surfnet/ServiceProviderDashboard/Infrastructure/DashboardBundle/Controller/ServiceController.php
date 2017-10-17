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

use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\SamlServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\SupplierService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\EditServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Metadata\Exception\ParserException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var SamlServiceService
     */
    private $samlService;

    /**
     * @var SupplierService
     */
    private $supplierService;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var TicketService
     */
    private $ticketService;

    /**
     * @param CommandBus $commandBus
     * @param SamlServiceService $samlService
     * @param SupplierService $supplierService
     * @param AuthorizationService $authorizationService
     * @param \Surfnet\ServiceProviderDashboard\Application\Service\TicketService $ticketService
     */
    public function __construct(
        CommandBus $commandBus,
        SamlServiceService $samlService,
        SupplierService $supplierService,
        AuthorizationService $authorizationService,
        TicketService $ticketService
    ) {
        $this->commandBus = $commandBus;
        $this->samlService = $samlService;
        $this->supplierService = $supplierService;
        $this->authorizationService = $authorizationService;
        $this->ticketService = $ticketService;
    }

    /**
     * @Method("GET")
     * @Route("/service/create", name="service_add")
     * @Security("has_role('ROLE_USER')")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction()
    {
        $supplier = $this->supplierService->getSupplierById(
            $this->authorizationService->getActiveSupplierId()
        );

        $serviceId = $this->samlService->createServiceId();
        $ticketNumber = $this->ticketService->getTicketIdForService($serviceId, $supplier);
        if (is_null($supplier)) {
            $this->get('logger')->error('Unable to find selected Supplier while creating a new Service');
            // Todo: show error page?
        }

        $command = new CreateServiceCommand($serviceId, $supplier, $ticketNumber);
        $this->commandBus->handle($command);

        return $this->redirectToRoute('service_edit', ['serviceId' => $serviceId]);
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/service/edit/{serviceId}", name="service_edit")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @param $serviceId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $serviceId)
    {
        $this->get('session')->getFlashBag()->clear();
        $service = $this->samlService->getServiceById($serviceId);

        $command = $this->samlService->buildEditServiceCommand($service);

        $form = $this->createForm(EditServiceType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                switch ($form->getClickedButton()->getName()) {
                    case 'importButton':
                        // Handle an import action based on the posted xml or import url.
                        $metadataCommand = new LoadMetadataCommand($command);
                        $this->commandBus->handle($metadataCommand);
                        return $this->redirectToRoute('service_edit', ['serviceId' => $service->getId()]);
                        break;
                    default:
                        $this->commandBus->handle($command);
                        return $this->redirectToRoute('service_list');
                        break;
                }
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (ParserException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('DashboardBundle:Service:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
