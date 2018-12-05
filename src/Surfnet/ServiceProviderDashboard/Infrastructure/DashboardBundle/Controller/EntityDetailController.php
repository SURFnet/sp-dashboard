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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetail;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EntityDetailController extends Controller
{
    /**
     * @Method("GET")
     * @Route("/entity/detail/{id}", name="entity_detail_draft")
     * @ParamConverter("entity", class="SurfnetServiceProviderDashboard:Entity")
     * @Security("has_role('ROLE_USER') and token.hasAccessToEntity(request.get('entity'))")
     * @Template("@Dashboard/EntityDetail/detail.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     * @param Entity $entity
     */
    public function detailDraftAction(Entity $entity)
    {
        $viewObject = EntityDetail::fromEntity($entity);

        return ['entity' => $viewObject];
    }
}
