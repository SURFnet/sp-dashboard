<?php

//declare(strict_types = 1);

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

use Exception;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InvalidEnvironmentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityResetSecretController extends AbstractController
{
    use EntityControllerTrait;

    /**
     *
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/entity/reset-secret/{serviceId}/{manageId}/{environment}', name: 'entity_reset_secret', methods: ['GET', 'POST'])]
    public function reset(Request $request, int $serviceId, string $manageId, string $environment): RedirectResponse
    {
        $request->getSession()->getFlashBag()->clear();

        $manageEntity = $this->entityService->getManageEntityById($manageId, $environment);
        $entityServiceId = $manageEntity->getService()->getId();
        if ($serviceId !== $entityServiceId) {
            throw $this->createAccessDeniedException(
                'You are not allowed to view an Entity from another Service'
            );
        }
        // Verify the Entity Service Id is one of the logged in users services
        $this->authorizationService->assertServiceIdAllowed($entityServiceId);

        $resetOidcSecretCommand = new ResetOidcSecretCommand($manageEntity);
        try {
            $this->commandBus->handle($resetOidcSecretCommand);
        } catch (Exception) {
            $this->addFlash('error', 'entity.edit.error.publish');
        }
        // A clone is saved in session temporarily, to be able to report which entity was removed on the reporting
        // page we will be redirecting to in a moment.
        $request->getSession()->set('published.entity.clone', clone $manageEntity);
        switch ($manageEntity->getEnvironment()) {
            case Constants::ENVIRONMENT_TEST:
                $destination = 'entity_published_test';
                return $this->redirectToRoute($destination);
            case Constants::ENVIRONMENT_PRODUCTION:
                $destination = 'entity_published_production';
                return $this->redirectToRoute($destination);
            default:
                throw new InvalidEnvironmentException(
                    sprintf('The environment with value "%s" is not supported.', $manageEntity->getEnvironment())
                );
        }
    }
}
