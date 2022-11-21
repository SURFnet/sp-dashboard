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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Command\PrivacyQuestions\PrivacyQuestionsCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\PrivacyQuestions\PrivacyQuestionsType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyQuestionsController extends AbstractController
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

    public function __construct(
        CommandBus $commandBus,
        ServiceService $serviceService,
        AuthorizationService $authorizationService
    ) {
        $this->commandBus = $commandBus;
        $this->serviceService = $serviceService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @Route("/service/{serviceId}/privacy", name="privacy_questions", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @param int $serviceId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function privacyAction($serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);

        if (!$this->authorizationService->hasActivatedPrivacyQuestions()) {
            throw $this->createNotFoundException('Privacy questions are disabled');
        }

        // Test if the questions have already been filled
        if ($service->getPrivacyQuestions() instanceof PrivacyQuestions) {
            return $this->forward('DashboardBundle:PrivacyQuestions:edit', ['serviceId' => $serviceId]);
        }
        return $this->forward('DashboardBundle:PrivacyQuestions:create', ['serviceId' => $serviceId]);
    }

    /**
     * @Route("/service/{serviceId}/privacy/create", name="privacy_questions_create")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     *
     * @param $serviceId
     * @return Response
     */
    public function createAction(Request $request, $serviceId)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);

        $command = PrivacyQuestionsCommand::fromService($service);

        return $this->renderForm($request, $command, $serviceId);
    }

    /**
     * @Route("/service/{serviceId}/privacy/edit", name="privacy_questions_edit")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     *
     * @param $serviceId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $serviceId)
    {
        $service = $this->serviceService->getServiceById($serviceId);

        $questions = $service->getPrivacyQuestions();

        $command = PrivacyQuestionsCommand::fromQuestions($questions);

        return $this->renderForm($request, $command, $serviceId);
    }

    /**
     * @param Request $request
     * @param PrivacyQuestionsCommand $command
     * @param int $serviceId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function renderForm(Request $request, PrivacyQuestionsCommand $command, $serviceId)
    {
        $form = $this->createForm(PrivacyQuestionsType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->addFlash('info', 'privacy.edit.flash.success');
            // Simply return to entity list, no entity was saved
            if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                return $this->redirectToRoute('service_admin_overview', ['serviceId' => $serviceId]);
            }
            return $this->redirectToRoute('service_overview');
        }

        return $this->render('@Dashboard/Privacy/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
