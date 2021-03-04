<?php

/**
 * Copyright 2018 SURFnet B.V.
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetail;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EntityDetailController extends Controller
{
    /**
     * @var EntityServiceInterface
     */
    private $entityService;
    /**
     * @var AuthorizationService
     */
    private $authorizationService;
    /**
     * @var string
     */
    private $playGroundUriTest;
    /**
     * @var string
     */
    private $playGroundUriProd;

    public function __construct(
        EntityServiceInterface $entityService,
        AuthorizationService $authorizationService,
        string $oidcPlaygroundUriTest,
        string $oidcPlaygroundUriProd
    ) {
        $this->entityService = $entityService;
        $this->authorizationService = $authorizationService;
        $this->playGroundUriTest = $oidcPlaygroundUriTest;
        $this->playGroundUriProd = $oidcPlaygroundUriProd;
    }

    /**
     * @Method("GET")
     * @Route("/entity/detail/{serviceId}/{id}/{manageTarget}", name="entity_detail", defaults={"manageTarget" = false})
     * @Security("has_role('ROLE_USER')")
     * @Template("@Dashboard/EntityDetail/detail.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function detailAction(string $id, int $serviceId, string $manageTarget)
    {
        $service = $this->authorizationService->changeActiveService($serviceId);
        $team = $service->getTeamName();
        /** @var ManageEntity $entity */
        $entity = $this->entityService->getEntityByIdAndTarget($id, $manageTarget, $service);
        if ($entity->getMetaData()->getCoin()->getServiceTeamId() !== $team) {
            $entity->setIsReadOnly();
        }
        $viewObject = EntityDetail::fromEntity($entity, $this->playGroundUriTest, $this->playGroundUriProd);

        return [
            'entity' => $viewObject,
            'isAdmin' => $this->isGranted('ROLE_ADMINISTRATOR'),
        ];
    }
}
