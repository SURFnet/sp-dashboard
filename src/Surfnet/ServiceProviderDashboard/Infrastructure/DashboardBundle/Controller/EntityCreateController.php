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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityCreateController extends Controller
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
     * @var ServiceService
     */
    private $serviceService;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var TicketService
     */
    private $ticketService;

    /**
     * @var MailMessageFactory
     */
    private $mailMessageFactory;

    /**
     * @param CommandBus $commandBus
     * @param EntityService $entityService
     * @param ServiceService $serviceService
     * @param AuthorizationService $authorizationService
     * @param \Surfnet\ServiceProviderDashboard\Application\Service\TicketService $ticketService
     */
    public function __construct(
        CommandBus $commandBus,
        EntityService $entityService,
        ServiceService $serviceService,
        AuthorizationService $authorizationService,
        TicketService $ticketService,
        MailMessageFactory $mailMessageFactory
    ) {
        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->serviceService = $serviceService;
        $this->authorizationService = $authorizationService;
        $this->ticketService = $ticketService;
        $this->mailMessageFactory = $mailMessageFactory;
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/entity/create", name="entity_add")
     * @Security("has_role('ROLE_USER')")
     * @Template("@Dashboard/EntityEdit/edit.html.twig")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createAction(Request $request)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        $service = $this->getService();
        $command = SaveEntityCommand::forCreateAction($service);

        $form = $this->createForm(EntityType::class, $command);
        $form->handleRequest($request);

        // Import metadata before loading data into the form
        if ($this->importButtonClicked($request)) {
            // Handle an import action based on the posted xml or import url.
            $metadataCommand = new LoadMetadataCommand($command, $request->get('dashboard_bundle_entity_type'));
            try {
                $this->commandBus->handle($metadataCommand);
            } catch (MetadataFetchException $e) {
                $this->addFlash('error', 'entity.edit.metadata.fetch.exception');
            } catch (ParserException $e) {
                $this->addFlash('error', 'entity.edit.metadata.parse.exception');
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
            $form = $this->createForm(EntityType::class, $command);
        }

        if ($form->isSubmitted()) {
            try {
                switch ($form->getClickedButton()->getName()) {
                    case 'save':
                        // Only trigger form validation on publish
                        $this->commandBus->handle($command);
                        return $this->redirectToRoute('entity_list');
                        break;
                    case 'publishButton':
                        // Only trigger form validation on publish
                        if ($form->isValid()) {
                            $this->commandBus->handle($command);
                            $entity = $this->entityService->getEntityById($command->getId());
                            $response = $this->publishEntity($entity, $flashBag);
                            // When a response is returned, publishing was a success
                            if ($response instanceof Response) {
                                return $response;
                            }
                            // When publishing failed, forward to the edit action and show the error messages there
                            return $this->redirectToRoute('entity_edit', ['id' => $entity->getId()]);
                        }
                        break;
                    case 'cancel':
                        // Simply return to entity list, no entity was saved
                        return $this->redirectToRoute('entity_list');
                        break;
                }
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function importButtonClicked(Request $request)
    {
        $data = $request->get('dashboard_bundle_entity_type', false);
        if ($data && isset($data['metadata']) && isset($data['metadata']['importButton'])) {
            return true;
        }
        return false;
    }

    private function publishEntity(Entity $entity, FlashBagInterface $flashBag)
    {
        switch ($entity->getEnvironment()) {
            case Entity::ENVIRONMENT_TEST:
                $publishEntityCommand = new PublishEntityTestCommand($entity->getId());
                $this->commandBus->handle($publishEntityCommand);

                if (!$flashBag->has('error')) {
                    $this->get('session')->set('published.entity.clone', clone $entity);

                    // Test entities are removed after they've been published to Manage
                    $deleteCommand = new DeleteEntityCommand($entity->getId());
                    $this->commandBus->handle($deleteCommand);

                    return $this->redirectToRoute('service_published_test');
                }
                break;

            case Entity::ENVIRONMENT_PRODUCTION:
                $message = $this->mailMessageFactory->buildPublishToProductionMessage($entity);
                $publishEntityCommand = new PublishEntityProductionCommand($entity->getId(), $message);
                $this->commandBus->handle($publishEntityCommand);
                return $this->redirectToRoute('service_published_production', ['id' => $entity->getId()]);
                break;
        }
    }

    private function getService()
    {
        $activeServiceId = $this->authorizationService->getActiveServiceId();
        if ($activeServiceId) {
            return $this->serviceService->getServiceById(
                $this->authorizationService->getActiveServiceId()
            );
        }
        return false;
    }
}
