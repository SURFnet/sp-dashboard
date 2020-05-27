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

use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PushMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Protocol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityResetSecretController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/reset-secret/{serviceId}/{manageId}/{environment}", name="entity_reset_secret")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @param string $manageId
     * @param string $environment
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function resetAction(Request $request, $serviceId, $manageId, $environment)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        $service = $this->serviceService->getServiceById($serviceId);

        $id = (string) Uuid::uuid1();

        $manageEntity = $this->entityService->getManageEntityById($manageId, $environment);
        if ($manageEntity->getProtocol()->getProtocol() === Protocol::OIDC10_RP &&
            !$this->authorizationService->isOidcngAllowed($service, $environment)
        ) {
            throw $this->createAccessDeniedException(
                'You are not allowed to reset the client secret for oidcng entities to this environment.'
            );
        }

        // fetch entity from manage and reset secret
        // the entity gets stored local temporarily
        $resetOidcSecretCommand = new ResetOidcSecretCommand($id, $manageId, $environment, $service);
        $this->commandBus->handle($resetOidcSecretCommand);

        // publish the entity
        $entity = $this->entityService->getEntityById($resetOidcSecretCommand->getId());
        $response = $this->publishEntity($entity, $flashBag);
        if ($entity->getEnvironment() === Entity::ENVIRONMENT_PRODUCTION) {
            // Push metadata (note that we also push to production manage upon client secret resets)
            // https://www.pivotaltracker.com/story/show/173009970
            $this->commandBus->handle(new PushMetadataCommand($entity->getEnvironment()));
        }
        if ($response instanceof Response) {
            return $response;
        }

        return $this->redirectToRoute('entity_list', ['serviceId' => $serviceId]);
    }
}
