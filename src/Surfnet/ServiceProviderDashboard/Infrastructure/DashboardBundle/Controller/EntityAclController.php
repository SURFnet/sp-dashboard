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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\AclEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetail;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
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
     * @var IdentityProviderRepository
     */
    private $identityProviderRepository;
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    public function __construct(
        CommandBus $commandBus,
        EntityService $entityService,
        AuthorizationService $authorizationService,
        IdentityProviderRepository $identityProviderRepository
    ) {

        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->authorizationService = $authorizationService;
        $this->identityProviderRepository = $identityProviderRepository;
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
        $flashBag = $this->get('session')->getFlashBag();

        $service = $this->authorizationService->getServiceById($serviceId);
        $entity = $this->entityService->getEntityByIdAndTarget($id, Entity::ENVIRONMENT_TEST, $service);

        $availableIdps = $this->identityProviderRepository->findAll();

        $command = new AclEntityCommand($availableIdps);
        $form = $this->createForm(AclEntityType::class, $command);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $selected = $command->getSelected();

            // handle selected in commandbus
        }
        $viewObject = EntityDetail::fromEntity($entity);


        return [
            'form' => $form->createView(),
            'entity' => $viewObject,
            'availableIdps' => $availableIdps,
        ];
    }
}
