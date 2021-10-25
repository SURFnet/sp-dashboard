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
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceStatusService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\SendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\PublishEntityClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TeamsController extends Controller
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
     * @var PublishEntityClient
     */
    private $publishEntityClient;

    /**
     * @var TranslatorInterface
     */
    private $translator;

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
        EntityService $entityService,
        PublishEntityClient $publishEntityClient,
        TranslatorInterface $translator
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationService = $authorizationService;
        $this->serviceService = $serviceService;
        $this->serviceStatusService = $serviceStatusService;
        $this->router = $router;
        $this->entityService = $entityService;
        $this->publishEntityClient = $publishEntityClient;
        $this->translator = $translator;
    }


    /**
     * @Method({"GET"})
     * @Route("/service/{serviceId}/manageTeam", name="service_manage_team")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     * @Template()
     *
     * @return RedirectResponse|Response
     */
    public function manageTeamAction(Request $request, int $serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);

        $this->get('session')->getFlashBag()->clear();
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');

        return $this->render('DashboardBundle:Teams:manage.html.twig', [
            'serviceId' => $serviceId,
            'teamId' => 2,
            'users' => [
                [
                    'invitationId' => 1,
                    'id' => 4,
                    'name' => '',
                    'email' => 'teun-fransen@hotmail.com',
                    'status' => 'Invitation',
                    'role' => 'Member',
                ],
                [
                    'id' => 1,
                    'name' => 'Henny Bekker',
                    'email' => 'henny.bekker@surfnet.nl',
                    'status' => 'March 1, 2011 2:41 PM',
                    'role' => 'Owner',
                ],
                [
                    'id' => 2,
                    'name' => 'Pieter van der Meulen',
                    'email' => 'pieter.vandermeulen@surfnet.nl',
                    'status' => 'March 27, 2014 5:21 PM',
                    'role' => 'Admin',
                ],
                [
                    'id' => 3,
                    'name' => 'Arnout Terpstra',
                    'email' => 'arnout@digimasters.nl',
                    'status' => 'February 7, 2018 3:38 PM',
                    'role' => 'Manager',
                ],
            ]
        ]);
    }

    /**
     * @Method({"POST"})
     * @Route("/service/{serviceId}/sendInvite/{teamId}", name="team_send_invite")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     *
     * @return RedirectResponse|Response
     */
    public function sendInviteAction(Request $request, int $serviceId, int $teamId)
    {
        $this->get('session')->getFlashBag()->clear();
        $email = $request->get('email');
        $role = $request->get('role');
        $invite = [
            'teamId' => $teamId,
            'intendedRole' => $role,
            'emails' => $this->createEmailsArray($email, $role),
            'message' => $this->translator->trans('teams.create.invitationMessage'),
            'language' => 'ENGLISH',
        ];

        try {
            $this->publishEntityClient->inviteMember($invite);
        } catch (SendInviteException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('service_manage_team', [ 'serviceId' => $serviceId ]);
    }

    /**
     * @Method({"GET"})
     * @Route("/service/{serviceId}/resendInvite/{invitationId}", name="team_resend_invite")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     * @Template()
     *
     * @return RedirectResponse|Response
     */
    public function resendInviteAction(Request $request, int $serviceId, int $invitationId) {

    }

    /**
     * @Method({"GET"})
     * @Route("/service/{serviceId}/delete/{memberId}", name="team_delete_member")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     * @Template()
     *
     * @return RedirectResponse|Response
     */
    public function deleteMemberAction(Request $request, int $serviceId, int $memberId) {

    }

    private function createEmailsArray(string $email, string $role): array
    {
        $emails = [];
        foreach (explode(',', $email) as $mail) {
            $emails[trim($mail)] = $role;
        }

        return $emails;
    }
}
