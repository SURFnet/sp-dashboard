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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EntityDetailController extends Controller
{
    /**
     * @var EntityServiceInterface
     */
    private $entityService;

    public function __construct(EntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
    }

    /**
     * @Method("GET")
     * @Route("/entity/detail/{serviceId}/{id}/{manageTarget}", name="entity_detail", defaults={"manageTarget" = false})
     * @Security("has_role('ROLE_USER')")
     * @Template("@Dashboard/EntityDetail/detail.html.twig")
     *
     * @param string $id
     * @param int $serviceId
     * @param string $manageTarget
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function detailAction($id, $serviceId, $manageTarget)
    {
        // First try to read the entity from the local storage
        $entity = $this->entityService->getEntityById($id);
        if (!$entity) {
            $entity = $this->entityService->getEntityByIdAndTarget($id, $manageTarget, $serviceId);
        }
        $viewObject = EntityDetail::fromEntity($entity);

        return ['entity' => $viewObject];
    }
}
