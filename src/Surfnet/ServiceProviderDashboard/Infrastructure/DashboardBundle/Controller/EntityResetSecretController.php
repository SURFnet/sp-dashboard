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

use Exception;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InvalidEnvironmentException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        $manageEntity = $this->entityService->getManageEntityById($manageId, $environment);
        $manageEntity->setService($service);

        $resetOidcSecretCommand = new ResetOidcSecretCommand($manageEntity);
        try {
            $this->commandBus->handle($resetOidcSecretCommand);
        } catch (Exception $e) {
            $flashBag->add('error', 'entity.edit.error.publish');
        }
        // A clone is saved in session temporarily, to be able to report which entity was removed on the reporting
        // page we will be redirecting to in a moment.
        $this->get('session')->set('published.entity.clone', clone $manageEntity);
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
