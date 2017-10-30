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
use Surfnet\ServiceProviderDashboard\Application\Command\PrivacyQuestions\PrivacyQuestionsCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\PrivacyQuestions\PrivacyQuestionsType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyQuestionsController extends Controller
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
     * @Method({"GET", "POST"})
     * @Route("/service/privacy", name="privacy_questions")
     * @Security("has_role('ROLE_USER')")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function privacyAction()
    {
        if (!$this->authorizationService->hasActivatedPrivacyQuestions()) {
            throw $this->createNotFoundException('Privacy questions are disabled');
        }

        $service = $this->serviceService->getServiceById(
            $this->authorizationService->getActiveServiceId()
        );

        // Test if the questions have already been filled
        if ($service->getPrivacyQuestions() instanceof PrivacyQuestions) {
            return $this->forward('DashboardBundle:PrivacyQuestions:edit');
        }
        return $this->forward('DashboardBundle:PrivacyQuestions:create');
    }

    /**
     * @Route("/service/privacy/create", name="privacy_questions_create")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $service = $this->serviceService->getServiceById(
            $this->authorizationService->getActiveServiceId()
        );

        $command = PrivacyQuestionsCommand::fromService($service);

        return $this->renderForm($request, $command);
    }

    /**
     * @Route("/service/privacy/edit", name="privacy_questions_edit")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request)
    {
        $service = $this->serviceService->getServiceById(
            $this->authorizationService->getActiveServiceId()
        );
        $questions = $service->getPrivacyQuestions();

        $command = PrivacyQuestionsCommand::fromQuestions($questions);

        return $this->renderForm($request, $command);
    }

    private function renderForm(Request $request, PrivacyQuestionsCommand $command)
    {
        $form = $this->createForm(PrivacyQuestionsType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            return $this->redirectToRoute('privacy_questions');
        }

        return $this->render('@Dashboard/Privacy/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
