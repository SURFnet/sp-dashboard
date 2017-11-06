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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\UpdateEntityStatusCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EditEntityType;
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
     */
    public function editAction(Request $request, Entity $entity)
    {
        $flashBag = $this->get('session')->getFlashBag();
        $flashBag->clear();

        if ($entity->isPublished() && $entity->isProduction()) {
            $updateStatusCommand = new UpdateEntityStatusCommand($entity->getId(), Entity::STATE_DRAFT);
            $this->commandBus->handle($updateStatusCommand);
        }

        $command = $this->entityService->buildEditEntityCommand($entity);

        $form = $this->createForm(EditEntityType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Save the changes made on the form
            $this->commandBus->handle($command);

            try {
                switch ($form->getClickedButton()->getName()) {
                    case 'importButton':
                        // Handle an import action based on the posted xml or import url.
                        $metadataCommand = LoadMetadataCommand::fromEditCommand($command);
                        $this->commandBus->handle($metadataCommand);
                        return $this->redirectToRoute('entity_edit', ['id' => $entity->getId()]);
                        break;

                    case 'publishButton':
                        // Only trigger form validation on publish
                        if ($form->isValid()) {
                            $response = $this->publishEntity($entity, $flashBag);
                            if ($response instanceof Response) {
                                return $response;
                            }
                        }
                        break;
                    default:
                        $this->commandBus->handle($command);
                        return $this->redirectToRoute('entity_list');
                        break;
                }
            } catch (MetadataFetchException $e) {
                $this->addFlash('error', 'entity.edit.metadata.fetch.exception');
            } catch (ParserException $e) {
                $this->addFlash('error', 'entity.edit.metadata.parse.exception');
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
}
