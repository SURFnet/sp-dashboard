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
     * @Route("/service/{serviceId}/privacy", name="privacy_questions")
     * @Security("has_role('ROLE_USER')")
     *
     * @param int $serviceId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function privacyAction($serviceId)
    {
        $service = $this->authorizationService->getServiceById($serviceId);

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
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     *
     * @param $serviceId
     * @return Response
     */
    public function createAction(Request $request, $serviceId)
    {
        $service = $this->authorizationService->getServiceById($serviceId);

        $command = PrivacyQuestionsCommand::fromService($service);

        return $this->renderForm($request, $command, $serviceId);
    }

    /**
     * @Route("/service/{serviceId}/privacy/edit", name="privacy_questions_edit")
     * @Security("has_role('ROLE_USER')")
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
            return $this->redirectToRoute('privacy_questions', ['serviceId' => $serviceId]);
        }

        return $this->render('@Dashboard/Privacy/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
