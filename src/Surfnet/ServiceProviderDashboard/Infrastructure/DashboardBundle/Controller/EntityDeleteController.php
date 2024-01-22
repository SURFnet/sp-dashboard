<?php

//declare(strict_types = 1);

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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteCommandFactory;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\DeleteEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntityDeleteController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EntityService $entityService,
        private readonly DeleteCommandFactory $commandFactory,
        private readonly AuthorizationService $authorizationService,
    ) {
    }

    /**
     * @param  int         $serviceId
     * @param  string|null $manageId
     * @param  string|null $environment
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/entity/delete/published/{serviceId}/{manageId}/{environment}',
        name: 'entity_delete_published',
        defaults: [
            'manageId' => null,
            'environment' => 'test'
        ],
        methods: ['GET', 'POST']
    )]
    public function deletePublished(
        Request $request,
        $serviceId,
        $manageId,
        $environment
    ): RedirectResponse|Response {
        $this->denyAccessUnlessGranted(
            "MANAGE_ENTITY_ACCESS",
            ['manageId' => $manageId, 'environment' => $environment]
        );

        $entity = $this->entityService->getManageEntityById($manageId, $environment);
        $nameEn = $entity->getMetaData()->getNameEn();
        $excludeFromPush = $entity->getMetaData()->getCoin()->getExcludeFromPush();

        $form = $this->createForm(DeleteEntityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $command = $this->commandFactory->buildDeletePublishedProductionEntityCommand(
                    $manageId,
                    $entity->getProtocol()->getProtocol()
                );
                if ($environment === 'test') {
                    $command = $this->commandFactory
                        ->buildDeletePublishedTestEntityCommand($manageId, $entity->getProtocol()->getProtocol());
                }
                $this->commandBus->handle($command);

                $this->addFlash('info', 'entity.delete.flash.success');
            }
            $service = $this->authorizationService->changeActiveService($serviceId);
            if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                return $this->redirectToRoute('service_admin_overview', ['serviceId' => $service->getId()]);
            }

            return $this->redirectToRoute('service_overview');
        }

        return $this->render(
            '@Dashboard/EntityDelete/delete.html.twig',
            [
            'form' => $form->createView(),
            'environment' => $environment,
            'status' => $excludeFromPush === "1" ? Constants::STATE_PUBLICATION_REQUESTED : Constants::STATE_PUBLISHED,
            'entityName' => $nameEn,
            ]
        );
    }

    /**
     * @param  int         $serviceId
     * @param  string|null $manageId
     * @param  string|null $environment
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        path: '/entity/delete/request/{serviceId}/{manageId}/{environment}',
        name: 'entity_delete_request',
        defaults: [
            'manageId' => null,
            'environment' => 'production'
        ],
        methods: ['GET', 'POST']
    )]
    public function deleteRequest(Request $request, $serviceId, $manageId, $environment): RedirectResponse|Response
    {
        $this->denyAccessUnlessGranted(
            "MANAGE_ENTITY_ACCESS",
            ['manageId' => $manageId, 'environment' => $environment]
        );

        $entity = $this->entityService->getManageEntityById($manageId, $environment);
        $nameEn = $entity->getMetaData()->getNameEn();

        $form = $this->createForm(DeleteEntityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $contact = $this->authorizationService->getContact();
                $this->commandBus->handle(
                    $this->commandFactory->buildRequestDeletePublishedEntityCommand(
                        $manageId,
                        $contact
                    )
                );
            }
            $service = $this->authorizationService->changeActiveService($serviceId);
            if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                return $this->redirectToRoute('service_admin_overview', ['serviceId' => $service->getId()]);
            }

            return $this->redirectToRoute('service_overview');
        }

        return $this->render(
            '@Dashboard/EntityDelete/delete.html.twig',
            [
                'form' => $form->createView(),
                'environment' => $environment,
                'status' => Constants::STATE_PUBLISHED,
                'entityName' => $nameEn,
            ]
        );
    }
}
