<?php

/**
 * Copyright 2021 SURFnet B.V.
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
use GuzzleHttp\Exception\GuzzleException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ServiceType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ChangeMembershipRoleException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ResendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\RuntimeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\SendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\UnableToDeleteMembershipException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\DeleteEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\QueryClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class TeamsController extends Controller
{
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var DeleteEntityClient
     */
    private $deleteEntityClient;

    /**
     * @var PublishEntityClient
     */
    private $publishEntityClient;

    /**
     * @var QueryClient
     */
    private $queryClient;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $defaultStemName;

    public function __construct(
        AuthorizationService $authorizationService,
        DeleteEntityClient $deleteEntityClient,
        PublishEntityClient $publishEntityClient,
        QueryClient $queryClient,
        TranslatorInterface $translator,
        string $defaultStemName
    ) {
        $this->authorizationService = $authorizationService;
        $this->deleteEntityClient = $deleteEntityClient;
        $this->publishEntityClient = $publishEntityClient;
        $this->queryClient = $queryClient;
        $this->translator = $translator;
        $this->defaultStemName = $defaultStemName;
    }

    /**
     * @Method({"GET"})
     * @Route("/service/{serviceId}/manageTeam", name="service_manage_team")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     *
     * @return Response
     * @throws GuzzleException
     */
    public function manageTeamAction(int $serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $sanitizedTeamName = str_replace($this->defaultStemName, '', $service->getTeamName());

        try {
            $teamInfo = $this->queryClient->findTeamByUrn($sanitizedTeamName);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $teamInfo['serviceId'] = $serviceId;

        return $this->render('DashboardBundle:Teams:manage.html.twig', $teamInfo);
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
        $role = strtoupper($request->get('role'));
        $invite = [
            'teamId' => $teamId,
            'intendedRole' => $role,
            'emails' => $this->createEmailsArray($email),
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
     */
    public function resendInviteAction(int $serviceId, int $invitationId): JsonResponse
    {
        $message = $this->translator->trans('teams.create.invitationMessage');

        try {
            $this->publishEntityClient->resendInvitation($invitationId, $message);
            $response = new JsonResponse('ok');
            $response->setStatusCode(200);
        } catch (ResendInviteException $e) {
            $response = new JsonResponse($e->getMessage());
            $response->setStatusCode(406);
        }

        return $response;
    }

    /**
     * @Method({"GET"})
     * @Route("/teams/changeRole/{memberId}/{newRole}", name="team_change_role")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     */
    public function changeRoleAction(int $memberId, string $newRole): JsonResponse
    {
        try {
            $this->publishEntityClient->changeMembership($memberId, $newRole);
            $response = new JsonResponse('ok');
            $response->setStatusCode(200);
        } catch (ChangeMembershipRoleException $e) {
            $response = new JsonResponse($e->getMessage());
            $response->setStatusCode(406);
        }

        return $response;
    }

    /**
     * @Method({"GET"})
     * @Route("/teams/delete/{memberId}", name="team_delete_member")
     * @Security("has_role('ROLE_ADMINISTRATOR')")
     */
    public function deleteMemberAction(int $memberId): JsonResponse
    {
        try {
            $this->deleteEntityClient->deleteMembership($memberId);
            $response = new JsonResponse('success');
            $response->setStatusCode(200);
        } catch (UnableToDeleteMembershipException|Exception|GuzzleException $e) {
            $response = new JsonResponse($e->getMessage());
            $response->setStatusCode(406);
        }

        return $response;
    }

    private function createEmailsArray(string $email): array
    {
        $emails = [];
        foreach (explode(',', $email) as $mail) {
            $emails[] = trim($mail);
        }

        return $emails;
    }
}
