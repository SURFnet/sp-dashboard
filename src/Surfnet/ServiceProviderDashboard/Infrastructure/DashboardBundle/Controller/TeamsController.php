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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\ResendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\SendInviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\DeleteEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\QueryClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TeamsController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
        private readonly DeleteEntityClient $deleteEntityClient,
        private readonly PublishEntityClient $publishEntityClient,
        private readonly QueryClient $queryClient,
        private readonly TranslatorInterface $translator,
        private readonly string $defaultStemName,
    ) {
    }

    /**
     *
     */
    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[Route(path: '/service/{serviceId}/manageTeam', name: 'service_manage_team', methods: ['GET'])]
    public function manageTeam(int $serviceId): Response
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $sanitizedTeamName = str_replace($this->defaultStemName, '', $service->getTeamName());

        try {
            $teamInfo = $this->queryClient->findTeamByUrn($sanitizedTeamName);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $teamInfo['serviceId'] = $serviceId;

        return $this->render('@Dashboard/Teams/manage.html.twig', $teamInfo);
    }

    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[Route(path: '/service/{serviceId}/sendInvite/{teamId}', name: 'team_send_invite', methods: ['POST'])]
    public function sendInvite(Request $request, int $serviceId, int $teamId): RedirectResponse
    {
        $request->getSession()->getFlashBag()->clear();
        $email = $request->get('email');
        $role = strtoupper((string) $request->get('role'));
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
     *
     */
    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[Route(path: '/service/{serviceId}/resendInvite/{invitationId}', name: 'team_resend_invite', methods: ['GET'])]
    public function resendInvite(int $serviceId, int $invitationId): JsonResponse
    {
        $message = $this->translator->trans('teams.create.invitationMessage');

        try {
            $this->publishEntityClient->resendInvitation($invitationId, $message);
            $response = new JsonResponse('ok');
            $response->setStatusCode(Response::HTTP_OK);
        } catch (ResendInviteException $e) {
            $response = new JsonResponse($e->getMessage());
            $response->setStatusCode(Response::HTTP_NOT_ACCEPTABLE);
        }

        return $response;
    }

    /**
     *
     */
    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[Route(path: '/teams/changeRole/{memberId}/{newRole}', name: 'team_change_role', methods: ['GET'])]
    public function changeRole(int $memberId, string $newRole): JsonResponse
    {
        try {
            $this->publishEntityClient->changeMembership($memberId, $newRole);
            $response = new JsonResponse('ok');
            $response->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            $response = new JsonResponse($e->getMessage());
            $response->setStatusCode(Response::HTTP_NOT_ACCEPTABLE);
        }

        return $response;
    }

    /**
     *
     */
    #[IsGranted('ROLE_ADMINISTRATOR')]
    #[Route(path: '/teams/delete/{memberId}', name: 'team_delete_member', methods: ['GET'])]
    public function deleteMember(int $memberId): JsonResponse
    {
        try {
            $this->deleteEntityClient->deleteMembership($memberId);
            $response = new JsonResponse('success');
            $response->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            $response = new JsonResponse($e->getMessage());
            $response->setStatusCode(Response::HTTP_NOT_ACCEPTABLE);
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
