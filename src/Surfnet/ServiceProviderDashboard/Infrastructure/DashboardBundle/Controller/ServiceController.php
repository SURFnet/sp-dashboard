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

use Exception;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\DeleteServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Exception\ServiceNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Service;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\ServiceList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryTeamsRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\ResetServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\SelectServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\CreateServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\DeleteServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\EditServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ServiceSwitcherType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly AuthorizationService $authorizationService,
        private readonly ServiceService $serviceService,
        private readonly ServiceStatusService $serviceStatusService,
        private readonly RouterInterface $router,
        private readonly EntityService $entityService,
        private readonly QueryTeamsRepository $queryClient,
        private readonly LoggerInterface $logger,
        private readonly string $defaultStemName
    ) {
    }

    /**
     * @Route("/", name="service_overview", methods={"GET"})
     * @Security("has_role('ROLE_USER')")
     */
    public function overviewAction()
    {
        $allowedServices = $this->authorizationService->getAllowedServiceNamesById();
        $services = $this->serviceService->getServicesByAllowedServices($allowedServices);
        $this->authorizationService->resetService();

        if (empty($services)) {
            return $this->redirectToRoute('service_add');
        }

        if ($this->isGranted('ROLE_ADMINISTRATOR')) {
            return $this->render("@Dashboard/Service/admin_overview.html.twig");
        }

        $serviceObjects = [];
        $privacyOK = [];
        foreach ($services as $service) {
            $entityList = $this->entityService->getEntityListForService($service);
            $serviceObjects[] = Service::fromService($service, $entityList, $this->router);
            $privacyOK[] = $this->serviceStatusService->hasPrivacyQuestions($service);
        }
        $serviceList = new ServiceList($serviceObjects);

        // Try to get a published entity from the session, if there is one, we just published an entity and might need
        // to display the oidc confirmation popup.
        /** @var ManageEntity $publishedEntity */
        $publishedEntity = $this->get('session')->get('published.entity.clone');

        return $this->render('@Dashboard/Service/overview.html.twig', [
            'services' => $serviceList,
            'isAdmin' => false,
            'publishedEntity' => $publishedEntity,
            'showOidcPopup' => $this->showOidcPopup($publishedEntity),
            'privacyStatusEntities' => $privacyOK,
        ]);
    }

    /**
     * @Route("/service/create", name="service_add", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMINISTRATOR')")
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $this->get('session')->getFlashBag()->clear();
        $command = new CreateServiceCommand();

        $form = $this->createForm(CreateServiceType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->logger->info(sprintf('Save new Service, service was created by: %s', '@todo'), (array) $command);

                try {
                    $this->commandBus->handle($command);
                    $this->get('session')->getFlashBag()->add('info', 'service.create.flash.success');
                    return $this->redirectToRoute('service_overview');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('@Dashboard/Service/create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/service/{serviceId}/edit", name="service_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMINISTRATOR')")
     *
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, int $serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);

        $this->get('session')->getFlashBag()->clear();

        $command = new EditServiceCommand(
            $service->getId(),
            $service->getGuid(),
            $service->getName(),
            $service->getTeamName(),
            $service->isProductionEntitiesEnabled(),
            $service->isPrivacyQuestionsEnabled(),
            $service->isClientCredentialClientsEnabled(),
            $service->getServiceType(),
            $service->getIntakeStatus(),
            $service->getContractSigned(),
            $service->getSurfconextRepresentativeApproved(),
            $this->serviceStatusService->hasPrivacyQuestions($service),
            $service->getInstitutionId(),
            $service->getOrganizationNameNl(),
            $service->getOrganizationNameEn()
        );

        $form = $this->createForm(EditServiceType::class, $command);
        $form->handleRequest($request);

        // On delete, forward to the service delete confirmation page.
        if ($this->isDeleteAction($form)) {
            $this->logger->info('Forwarding to the delete confirmation page');
            return $this->redirectToRoute('service_delete', ['serviceId' => $serviceId]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info(sprintf('Service was edited by: "%s"', '@todo'), (array)$command);
            try {
                $this->commandBus->handle($command);
                $this->get('session')->getFlashBag()->add('info', 'service.edit.flash.success');
                return $this->redirectToRoute('service_admin_overview', ['serviceId' => $serviceId]);
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (EntityNotFoundException $e) {
                $this->addFlash('error', 'The Service could not be found while handling the request');
            }
        }

        return $this->render('@Dashboard/Service/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/service/{serviceId}/delete", name="service_delete", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMINISTRATOR')")
     *
     * @param Request $request
     * @param $serviceId
     * @return RedirectResponse|Response
     */
    public function deleteAction(Request $request, $serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);

        $form = $this->createForm(DeleteServiceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $service = $this->serviceService->getServiceById($serviceId);

                // Get teaminfo for id
                $sanitizedTeamName = str_replace($this->defaultStemName, '', $service->getTeamName());

                try {
                    $teamInfo = $this->queryClient->findTeamByUrn($sanitizedTeamName);
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('service_admin_overview', ['serviceId' => $serviceId]);
                }

                $teamId = $teamInfo['teamId'];

                // Remove the service
                $contact = $this->authorizationService->getContact();
                $command = new DeleteServiceCommand($service->getId(), $contact, $teamId);
                $this->commandBus->handle($command);

                // Reset the service switcher (the currently active service was just removed)
                $resetCommand = new ResetServiceCommand();
                $this->commandBus->handle($resetCommand);

                $this->get('session')->getFlashBag()->add('info', 'service.delete.flash.success');
            }

            return $this->redirectToRoute('service_overview');
        }

        return $this->render('@Dashboard/Service/delete.html.twig', [
            'form' => $form->createView(),
            'serviceName' => $service->getName(),
            'entityList' => $this->entityService->getEntityListForService($service),
        ]);
    }

    /**
     * @Route("/service/select", name="select_service", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function selectAction(Request $request)
    {
        $command = new SelectServiceCommand();
        $form = $this->createForm(ServiceSwitcherType::class, $command);

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            throw new ServiceNotFoundException('Unable to find service.');
        }

        $this->commandBus->handle($command);

        return $this->redirectToRoute('service_admin_overview', ['serviceId' => $command->getSelectedServiceId()]);
    }

    /**
     * @Route("/service/{serviceId}", name="service_admin_overview", methods={"GET"})
     * @Security("is_granted('ROLE_ADMINISTRATOR')")
     */
    public function adminOverviewAction($serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $entityList = $this->entityService->getEntityListForService($service);
        $serviceList = new ServiceList([Service::fromService($service, $entityList, $this->router)]);
        $privacyOK = [$this->serviceStatusService->hasPrivacyQuestions($service)];

        // Try to get a published entity from the session, if there is one, we just published an entity and might need
        // to display the oidc confirmation popup.
        /** @var ManageEntity $publishedEntity */
        $publishedEntity = $this->get('session')->get('published.entity.clone');

        return $this->render('@Dashboard/Service/overview.html.twig', [
            'services' => $serviceList,
            'isAdmin' => true,
            'showOidcPopup' => $this->showOidcPopup($publishedEntity),
            'publishedEntity' => $publishedEntity,
            'privacyStatusEntities' => $privacyOK,
        ]);
    }

    private function isDeleteAction(FormInterface $form): bool
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
    private function assertUsedSubmitButton(FormInterface $form, $expectedButtonName)
    {
        $button = $form->getClickedButton();

        if ($button === null) {
            return false;
        }

        return $button->getName() === $expectedButtonName;
    }

    private function showOidcPopup(?ManageEntity $publishedEntity)
    {
        if (is_null($publishedEntity)) {
            return false;
        }
        $protocol = $publishedEntity->getProtocol()->getProtocol();
        $protocolUsesSecret = $protocol === Constants::TYPE_OPENID_CONNECT_TNG ||
            $protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER ||
            $protocol === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT;

        return $publishedEntity && $protocolUsesSecret && $publishedEntity->getOidcClient()->getClientSecret();
    }
}
