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

use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityOidcConfirmation;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EntityPublishedController extends AbstractController
{
    /**
     *
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/entity/published/production', name: 'entity_published_production', methods: ['GET'])]
    #[Route(path: '/entity/published/test', name: 'entity_published_test', methods: ['GET'])]
    public function published(Request $request): RedirectResponse|Response
    {
        /**
 * @var ManageEntity $entity
*/
        $entity = $request->getSession()->get('published.entity.clone');

        // Redirects OIDC published entity confirmations to the entity list page and shows a
        // confirmation dialog in a modal window that renders the oidcConfirmationModalAction
        $protocol = $entity->getProtocol()->getProtocol();
        if ($protocol === Constants::TYPE_OPENID_CONNECT_TNG
            || $protocol === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER
            || $protocol === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT
        ) {
            if ($this->isGranted('ROLE_ADMINISTRATOR')) {
                return $this->redirectToRoute('service_admin_overview', ['serviceId' => $entity->getService()->getId()]);
            }

            return $this->redirectToRoute('service_overview');
        }

        $parameters = [
            'entityName' => $entity->getMetaData()->getNameEn(),
            'showOidcPopup' => false,
            'publishedEntity' => $entity,
        ];

        if ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST) {
            return $this->render('@Dashboard/EntityPublished/publishedTest.html.twig', $parameters);
        }
        return $this->render('@Dashboard/EntityPublished/publishedProduction.html.twig', $parameters);
    }

    /**
     *
     */
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/entity/change-request', name: 'entity_change_request', methods: ['GET'])]
    public function changeRequest(): Response
    {
        return $this->render('@Dashboard/EntityPublished/changeRequested.html.twig');
    }

    /**
     * Show the confirmation popup for an OpenID connect entity
     *
     * In this popup the client id and the secret are displayed (once)
     *
     * This action is rendered inside a modal window, and is triggered from the
     * entity list action.
     *
     *
     */
    #[IsGranted('ROLE_USER')]
    public function oidcConfirmationModal(Request $request): Response
    {
        $entity = $request->getSession()->get('published.entity.clone');
        assert($entity instanceof ManageEntity);

        // Show the confirmation modal only once in this request
        $request->getSession()->remove('published.entity.clone');

        $viewObject = EntityOidcConfirmation::fromEntity($entity);

        return $this->render(
            '@Dashboard/EntityPublished/oidcConfirmationModal.html.twig',
            [
            'entity' => $viewObject,
            'environment' => $entity->getEnvironment(),
            ]
        );
    }
}
