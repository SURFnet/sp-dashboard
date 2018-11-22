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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteDraftEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedProductionEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeletePublishedTestEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\RequestDeletePublishedEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\DeleteEntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityDeleteController extends Controller
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
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus, EntityService $entityService)
    {
        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
    }

    /**
     * @Method({"GET", "POST"})
     * @ParamConverter("entity", class="SurfnetServiceProviderDashboard:Entity")
     * @Route("/entity/delete/{id}", name="entity_delete")
     * @Security("token.hasAccessToEntity(request.get('entity'))")
     * @Template()
     *
     * @param Request $request
     * @param Entity $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction(Request $request, Entity $entity)
    {
        $form = $this->createForm(DeleteEntityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $this->commandBus->handle(
                    new DeleteDraftEntityCommand($entity->getId())
                );
            }

            return $this->redirectToRoute('entity_list');
        }

        return [
            'form' => $form->createView(),
            'environment' => $entity->getEnvironment(),
            'status' => $entity->getStatus(),
            'entityName' => $entity->getNameEn(),
        ];
    }

    /**
     * @Method({"GET", "POST"})
     * @Route(
     *     "/entity/delete/published/{manageId}/{environment}",
     *     name="entity_delete_published",
     *     defaults={
     *          "manageId": null,
     *          "environment": "test"
     *     }
     * )
     * @Template("@Dashboard/EntityDelete/delete.html.twig")
     *
     * @param Request $request
     *
     * @param $manageId
     * @param $environment
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deletePublishedAction(Request $request, $manageId, $environment)
    {
        $entity = $this->entityService->getManageEntityById($manageId, $environment);
        $nameEn = $entity['data']['metaDataFields']['name:en'];

        $excludeFromPush = 0;
        if (isset($entity['data']['metaDataFields']['coin:exclude_from_push'])) {
            $excludeFromPush = $entity['data']['metaDataFields']['coin:exclude_from_push'];
        }

        $form = $this->createForm(DeleteEntityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $command = new DeletePublishedProductionEntityCommand($manageId);
                if ($environment === 'test') {
                    $command = new DeletePublishedTestEntityCommand($manageId);
                }
                $this->commandBus->handle($command);
            }

            return $this->redirectToRoute('entity_list');
        }

        return [
            'form' => $form->createView(),
            'environment' => $environment,
            'status' => $excludeFromPush === "1" ? 'requested' : 'published',
            'entityName' => $nameEn,
        ];
    }
    /**
     * @Method({"GET", "POST"})
     * @Route(
     *     "/entity/delete/request/{manageId}/{environment}",
     *     name="entity_delete_request",
     *     defaults={
     *          "manageId": null,
     *          "environment": "production"
     *     }
     * )
     * @Template("@Dashboard/EntityDelete/delete.html.twig")
     *
     * @param Request $request
     *
     * @param $manageId
     * @param $environment
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteRequestAction(Request $request, $manageId, $environment)
    {
        $entity = $this->entityService->getManageEntityById($manageId, $environment);
        $nameEn = $entity['data']['metaDataFields']['name:en'];

        $form = $this->createForm(DeleteEntityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'delete') {
                $this->commandBus->handle(
                    new RequestDeletePublishedEntityCommand($manageId)
                );
            }

            return $this->redirectToRoute('entity_list');
        }

        return [
            'form' => $form->createView(),
            'environment' => $environment,
            'status' => 'published',
            'entityName' => $nameEn,
        ];
    }
}
