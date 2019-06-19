<?php

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityAclCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityAclService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetail;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\AclEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityAclController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var EntityService
     */
    private $entityService;
    /**
     * @var AuthorizationService
     */
    private $authorizationService;
    /**
     * @var EntityAclService
     */
    private $entityAclService;

    public function __construct(
        CommandBus $commandBus,
        EntityService $entityService,
        AuthorizationService $authorizationService,
        EntityAclService $entityAclService
    ) {

        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->authorizationService = $authorizationService;
        $this->entityAclService = $entityAclService;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/acl/{serviceId}/{id}", name="entity_acl")
     * @Template()
     *
     * @param Request $request
     * @param string $serviceId
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function aclAction(Request $request, $serviceId, $id)
    {
        $service = $this->authorizationService->getServiceById($serviceId);
        $entity = $this->entityService->getEntityByIdAndTarget($id, Entity::ENVIRONMENT_TEST, $service);

        $selectedIdps = $this->entityAclService->getAllowedIdpsFromEntity($entity);

        $command = new UpdateEntityAclCommand($entity->getManageId(), $service->getId(), $selectedIdps, $entity->isIdpAllowAll());
        $form = $this->createForm(AclEntityType::class, $command);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
        }
        $viewObject = EntityDetail::fromEntity($entity);

        return [
            'form' => $form->createView(),
            'entity' => $viewObject,
        ];
    }
}
