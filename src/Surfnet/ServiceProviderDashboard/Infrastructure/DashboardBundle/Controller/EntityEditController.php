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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityStatusCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityEditController extends Controller
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
     * @var MailMessageFactory
     */
    private $mailMessageFactory;

    /**
     * @param CommandBus $commandBus
     * @param EntityService $entityService
     * @param ServiceService $serviceService
     * @param AuthorizationService $authorizationService
     * @param MailMessageFactory $mailMessageFactory
     */
    public function __construct(
        CommandBus $commandBus,
        EntityService $entityService,
        ServiceService $serviceService,
        AuthorizationService $authorizationService,
        MailMessageFactory $mailMessageFactory
    ) {
        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->serviceService = $serviceService;
        $this->authorizationService = $authorizationService;
        $this->mailMessageFactory = $mailMessageFactory;
    }

    /**
     * Subscribe to the PRE_SUBMIT form event to be able to import the metadata
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        );
    }

    /**
     * @Method({"GET", "POST"})
     * @ParamConverter("entity", class="SurfnetServiceProviderDashboard:Entity")
     * @Route("/entity/edit/{id}", name="entity_edit")
     * @Security("token.hasAccessToEntity(request.get('entity'))")
     * @Template()
     *
     * @param Request $request
     * @param Entity $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function editAction(Request $request, Entity $entity)
    {
        $flashBag = $this->get('session')->getFlashBag();

        // Only clear the flash bag when this request did not come from the 'entity_add' action.
        if (!$this->requestFromCreateAction($request)) {
            $flashBag->clear();
        }

        if ($entity->isPublished() && $entity->isProduction()) {
            $updateStatusCommand = new UpdateEntityStatusCommand($entity->getId(), Entity::STATE_DRAFT);
            $this->commandBus->handle($updateStatusCommand);
        }

        $command = SaveEntityCommand::fromEntity($entity);

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
                        $this->commandBus->handle($command);
                        return $this->redirectToRoute('entity_list');
                        break;
                    case 'publishButton':
                        // Only trigger form validation on publish
                        $this->commandBus->handle($command);
                        if ($form->isValid()) {
                            $response = $this->publishEntity($entity, $flashBag);
                            if ($response instanceof Response) {
                                return $response;
                            }
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

    private function importButtonClicked(Request $request)
    {
        $data = $request->get('dashboard_bundle_entity_type', false);
        if ($data && isset($data['metadata']) && isset($data['metadata']['importButton'])) {
            return true;
        }
        return false;
    }

    /**
     * When the create action (entity_add) unsuccessfully published an entity. The entity_edit action is loaded and the
     * manage error message (publication failed) message should be shown on the edit form.
     *
     * This method tests if the referer is set in the request headers, if so, it tests if the previous request
     * originated from the entity_add action.
     *
     * @param Request $request
     * @return bool
     */
    private function requestFromCreateAction(Request $request)
    {
        $requestUri = $request->headers->get('referer', false);
        if ($requestUri && preg_match('/\/entity\/create/', $requestUri)) {
            return true;
        }
        return false;
    }
}
