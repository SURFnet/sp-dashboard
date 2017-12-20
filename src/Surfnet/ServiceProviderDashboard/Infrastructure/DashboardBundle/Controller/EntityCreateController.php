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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CopyEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Exception\ServiceNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @param TicketService $ticketService
     * @param MailMessageFactory $mailMessageFactory
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
     * The create action serves two routes.
     *
     *  1. entity_add to create new entities.
     *
     *  2. entity_copy to copy entities from Manage into the SP Dashboard.
     *     The Entity is copied from the manage test environment to become either a SP Dashboard test-draft or
     *     production-draft entity.
     *
     * @Method({"GET", "POST"})
     * @Route("/entity/create", defaults={"manageId" = null, "environment" = null}, name="entity_add")
     * @Route("/entity/copy/{manageId}/{environment}", defaults={"manageId" = null, "environment" = "test"}, name="entity_copy")
     * @Security("has_role('ROLE_USER')")
     * @Template("@Dashboard/EntityEdit/edit.html.twig")
     *
     * @param Request $request
     *
     * @param null|string $manageId set from the entity_copy route
     * @param null|string $environment set from the entity_copy route
     *
     * @return RedirectResponse|Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createAction(Request $request, $manageId, $environment)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        $service = $this->getService();
        $command = SaveEntityCommand::forCreateAction($service);

        $form = $this->createForm(EntityType::class, $command);
        $form->handleRequest($request);

        if ($this->isCopyAction($request)) {
            $result = $form = $this->handleCopy($command, $service, $manageId, $environment);
            // The result of the copy handle method can either be a redirect response or a FormInterface.
            if ($result instanceof RedirectResponse) {
                return $result;
            }
        } elseif ($this->isImportButtonClicked($request)) {
            // Import metadata before loading data into the form. Rebuild the form with the imported data
            $form = $this->handleImport($request, $command);
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

    private function isImportButtonClicked(Request $request)
    {
        $data = $request->get('dashboard_bundle_entity_type', false);
        if ($data && isset($data['metadata']) && isset($data['metadata']['importButton'])) {
            return true;
        }
        return false;
    }

    private function isCopyAction(Request $request)
    {
        $currentRoute = $request->get('_route', false);
        // When the form is posted to the entity_copy endpoint, do NOT copy the data from manage. The user is about
        // to persist changes to manage.
        $isPostRequest = $request->getMethod() === 'POST';
        if ($currentRoute && $currentRoute === 'entity_copy' && !$isPostRequest) {
            return true;
        }
        return false;
    }

    /**
     * @param Entity $entity
     * @param FlashBagInterface $flashBag
     * @return RedirectResponse
     */
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

    /**
     * @return Service
     * @throws ServiceNotFoundException
     */
    private function getService()
    {
        $activeServiceId = $this->authorizationService->getActiveServiceId();
        if ($activeServiceId) {
            return $this->serviceService->getServiceById(
                $this->authorizationService->getActiveServiceId()
            );
        }
        throw new ServiceNotFoundException('Please select a service before adding/copying an entity.');
    }

    /**
     * @param Request $request
     * @param SaveEntityCommand $command
     *
     * @return FormInterface
     */
    private function handleImport(Request $request, SaveEntityCommand $command)
    {
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
        return $this->createForm(EntityType::class, $command);
    }

    /**
     * @param SaveEntityCommand $command
     * @param Service $service
     * @param $manageId
     * @param $environment
     *
     * @return FormInterface|RedirectResponse
     *
     * @throws InvalidArgumentException
     */
    private function handleCopy(SaveEntityCommand $command, Service $service, $manageId, $environment)
    {
        $existingEntity = $this->entityService->findTestEntityByManageId($manageId);
        if ($existingEntity) {
            return $this->redirectToRoute('entity_edit', ['id' => $existingEntity->getId()]);
        }

        $entityId = $this->entityService->createEntityUuid();

        $this->commandBus->handle(
            new CopyEntityCommand($command, $entityId, $manageId, $service, $environment)
        );

        return $this->createForm(EntityType::class, $command);
    }
}
