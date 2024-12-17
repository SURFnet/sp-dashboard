<?php

declare(strict_types = 1);

/**
 * Copyright 2019 SURFnet B.V.
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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PushMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityIdpsCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityAclService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\IdpEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityAclController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EntityService $entityService,
        private readonly AuthorizationService $authorizationService,
        private readonly EntityAclService $entityAclService,
    ) {
    }

    #[Route(path: '/entity/idps/{serviceId}/{id}', name: 'entity_acl_idps', methods: ['GET', 'POST'])]
    public function idps(Request $request, string $serviceId, string $id): Response
    {
        $allowed = $this->authorizationService->getAllowedServiceNamesById();
        if (!array_key_exists($serviceId, $allowed)) {
            throw $this->createAccessDeniedException(
                'You are not allowed to view ACLs of another service'
            );
        }
        $service = $this->authorizationService->changeActiveService($serviceId);
        $entity = $this->entityService->getEntityByIdAndTarget($id, Constants::ENVIRONMENT_TEST, $service);
        $selectedIdps = $this->entityAclService->getAllowedIdpsFromEntity($entity);

        $command = new UpdateEntityIdpsCommand(
            $entity,
            $selectedIdps,
            $selectedIdps,
            $this->authorizationService->getContact()
        );

        $form = $this->createForm(IdpEntityType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($command);
                $this->commandBus->handle(new PushMetadataCommand(Constants::ENVIRONMENT_TEST));

                // the new entity flow
                if ($request->getSession()->get('published.entity.clone') !== null) {
                    return $this->redirectToRoute('entity_published_test');
                }

                // edit idps for existing entity flow
                $this->addFlash('success', 'entity.idps.saved');
            } catch (Throwable $e) {
                $this->addFlash('error', 'entity.edit.error.publish');
            }
        }

        return $this->render(
            '@Dashboard/EntityAcl/idps.html.twig',
            [
                'form' => $form->createView(),
                'isAdmin' => $this->authorizationService->isAdministrator(),
            ]
        );
    }
}
