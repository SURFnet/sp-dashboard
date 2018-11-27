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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\DeleteServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\ResetServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Service;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\ServiceList;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\SelectServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\CreateServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\DeleteServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\EditServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var ServiceService
     */
    private $serviceService;

    /**
     * @var ServiceStatusService
     */
    private $serviceStatusService;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var EntityService
     */
    private $entityService;

    /**
     * @param CommandBus $commandBus
     * @param AuthorizationService $authorizationService
     * @param ServiceService $serviceService
     * @param ServiceStatusService $serviceStatusService
     */
    public function __construct(
        CommandBus $commandBus,
        AuthorizationService $authorizationService,
        ServiceService $serviceService,
        ServiceStatusService $serviceStatusService,
        RouterInterface $router,
        EntityService $entityService
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationService = $authorizationService;
        $this->serviceService = $serviceService;
        $this->serviceStatusService = $serviceStatusService;
        $this->router = $router;
        $this->entityService = $entityService;
    }

    /**
     * @Method({"GET"})
     * @Route("/", name="service_overview")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     */
    public function overviewAction()
    {
        $allowedServices = $this->authorizationService->getAllowedServiceNamesById();
        $services = $this->serviceService->getServicesByAllowedServices($allowedServices);

        if (empty($services)) {
            return $this->redirectToRoute('service_add');
        }


        $serviceObjects = [];
        foreach ($services as $service) {
            $entityList = $this->entityService->getEntityListForService($service);
            $serviceObjects[] = Service::fromService($service, $entityList, $this->router);
        }
        $serviceList = new ServiceList($serviceObjects);

        return $this->render('DashboardBundle:Service:overview.html.twig', [
            'services' => $serviceList
        ]);
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/service/create", name="service_add")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     * @Template()
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $this->get('session')->getFlashBag()->clear();
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');
        $command = new CreateServiceCommand();

        $form = $this->createForm(CreateServiceType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->info(sprintf('Save new Service, service was created by: %s', '@todo'), (array) $command);
            try {
                $this->commandBus->handle($command);
                return $this->redirectToRoute('entity_list');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('DashboardBundle:Service:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/service/edit", name="service_edit")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     * @Template()
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $this->get('session')->getFlashBag()->clear();
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');
        $service = $this->serviceService->getServiceById(
            $this->authorizationService->getActiveServiceId()
        );

        $command = new EditServiceCommand(
            $service->getId(),
            $service->getGuid(),
            $service->getName(),
            $service->getTeamName(),
            $service->isProductionEntitiesEnabled(),
            $service->isPrivacyQuestionsEnabled(),
            $service->getServiceType(),
            $service->getIntakeStatus(),
            $service->getContractSigned(),
            $service->getSurfconextRepresentativeApproved(),
            $this->serviceStatusService->hasPrivacyQuestions($service),
            $service->getConnectionStatus(),
            $this->serviceStatusService->getEntityStatus($service)
        );

        $form = $this->createForm(EditServiceType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On delete, forward to the service delete confirmation page.
            if ($this->isDeleteAction($form)) {
                $logger->info('Forwarding to the delete confirmation page');
                return $this->redirectToRoute('service_delete');
            }

            $logger->info(sprintf('Service was edited by: "%s"', '@todo'), (array)$command);
            try {
                $this->commandBus->handle($command);
                return $this->redirectToRoute('entity_list');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (EntityNotFoundException $e) {
                $this->addFlash('error', 'The Service could not be found while handling the request');
            }
        }

        return $this->render('DashboardBundle:Service:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/service/delete", name="service_delete")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        $form = $this->createForm(DeleteServiceType::class);
        $form->handleRequest($request);

        $service = $this->serviceService->getServiceById(
            $this->authorizationService->getActiveServiceId()
        );

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $service = $this->serviceService->getServiceById(
                    $this->authorizationService->getActiveServiceId()
                );

                // Remove the service
                $contact = $this->authorizationService->getContact();
                $command = new DeleteServiceCommand($service->getId(), $contact);
                $this->commandBus->handle($command);

                // Reset the service switcher (the currently active service was just removed)
                $resetCommand = new ResetServiceCommand();
                $this->commandBus->handle($resetCommand);
            }

            return $this->redirectToRoute('service_overview');
        }

        return [
            'form' => $form->createView(),
            'serviceName' => $service->getName()
        ];
    }
    /**
     * @Method({"GET", "POST"})
     * @Route("/service/select", name="select_service")
     * @Security("has_role('ROLE_USER')")
     */
    public function selectAction(Request $request)
    {
        $serviceId = $request->get('service', $request->query->get('service'));
        $command = new SelectServiceCommand(
            $serviceId
        );

        $this->commandBus->handle($command);

        return $this->redirectToRoute('entity_list');
    }

    /**
     * @param ServiceType $form
     * @return bool
     */
    private function isDeleteAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'delete');
    }

    /**
     * Check if the form was submitted using the given button name.
     *
     * @param EditServiceType $form
     * @param string $expectedButtonName
     * @return bool
     */
    private function assertUsedSubmitButton(Form $form, $expectedButtonName)
    {
        $button = $form->getClickedButton();

        if ($button === null) {
            return false;
        }

        return $button->getName() === $expectedButtonName;
    }
}
