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
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Supplier\CreateSupplierCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Supplier\EditSupplierCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Supplier\SelectSupplierCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\EditSupplierType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\CreateSupplierType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AdminSwitcherService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SupplierController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AdminSwitcherService
     */
    private $switcherService;

    /**
     * @param CommandBus $commandBus
     * @param AdminSwitcherService $switcherService
     */
    public function __construct(CommandBus $commandBus, AdminSwitcherService $switcherService)
    {
        $this->commandBus = $commandBus;
        $this->switcherService = $switcherService;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/supplier/create", name="supplier_add")
     * @Template()
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $this->get('session')->getFlashBag()->clear();
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');
        $command = new CreateSupplierCommand();

        $form = $this->createForm(CreateSupplierType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->info(sprintf('Save new Supplier, service was created by: %s', '@todo'), (array) $command);
            try {
                $this->commandBus->handle($command);
                return $this->redirectToRoute('service_list');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('DashboardBundle:Supplier:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/supplier/edit", name="supplier_edit")
     * @Template()
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $this->get('session')->getFlashBag()->clear();
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');
        $supplier = $this->switcherService->getSupplierById((int) $this->switcherService->getSelectedSupplier());

        $command = new EditSupplierCommand(
            $supplier->getId(),
            $supplier->getGuid(),
            $supplier->getName(),
            $supplier->getTeamName()
        );

        $form = $this->createForm(EditSupplierType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->info(sprintf('Supplier was edited by: "%s"', '@todo'), (array)$command);
            try {
                $this->commandBus->handle($command);
                return $this->redirectToRoute('service_list');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (EntityNotFoundException $e) {
                $this->addFlash('error', 'The Supplier could not be found while handling the request');
            }
        }

        return $this->render('DashboardBundle:Supplier:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Method("POST")
     * @Route("/supplier/select", name="select_supplier")
     */
    public function selectAction(Request $request)
    {
        $command = new SelectSupplierCommand(
            $request->request->get('supplier')
        );

        $this->commandBus->handle($command);

        return $this->redirectToRoute('service_list');
    }
}
