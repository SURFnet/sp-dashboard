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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityAclCommand;
use Surfnet\ServiceProviderDashboard\Application\Factory\EntityDetailFactory;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityAclService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\TestIdpService;
use Surfnet\ServiceProviderDashboard\Application\Service\TestIdpServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\AclEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        private readonly TestIdpServiceInterface $testIdpService,
        private readonly EntityDetailFactory $entityDetailFactory,
    ) {
    }

    #[Route(path: '/entity/idps/{serviceId}/{id}', name: 'entity_acl_idps', methods: ['GET', 'POST'])]
    public function idps(Request $request, string $serviceId, string $id): Response
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $entity = $this->entityService->getEntityByIdAndTarget($id, Constants::ENVIRONMENT_TEST, $service);
        $viewObject = $this->entityDetailFactory->buildFrom($entity);
        $testEntities = $this->testIdpService->loadTestIdps();

        return $this->render(
            '@Dashboard/EntityAcl/idps.html.twig',
            [
                'entity' => $viewObject,
                'testEntities' => $testEntities,
                'isAdmin' => $this->authorizationService->isAdministrator(),
            ]
        );
    }

    #[Route(path: '/entity/acl/{serviceId}/{id}', name: 'entity_acl', methods: ['GET', 'POST'])]
    public function acl(Request $request, string $serviceId, string $id): Response
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $entity = $this->entityService->getEntityByIdAndTarget($id, Constants::ENVIRONMENT_TEST, $service);

        $selectedIdps = $this->entityAclService->getAllowedIdpsFromEntity($entity);

        $command = new UpdateEntityAclCommand(
            $entity,
            $selectedIdps,
            $entity->getAllowedIdentityProviders()->isAllowAll()
        );
        $form = $this->createForm(AclEntityType::class, $command);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->commandBus->handle(new PushMetadataCommand(Constants::ENVIRONMENT_TEST));
        }

        $viewObject = $this->entityDetailFactory->buildFrom($entity);

        return $this->render(
            '@Dashboard/EntityAcl/acl.html.twig',
            [
            'form' => $form->createView(),
            'entity' => $viewObject,
            'isAdmin' => $this->authorizationService->isAdministrator(),
            ]
        );
    }
}
