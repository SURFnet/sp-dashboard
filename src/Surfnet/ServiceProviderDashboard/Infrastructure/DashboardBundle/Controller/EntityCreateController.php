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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        TicketService $ticketService
    ) {
        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->serviceService = $serviceService;
        $this->authorizationService = $authorizationService;
        $this->ticketService = $ticketService;
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

        $command = SaveEntityCommand::forCreateAction();

        $form = $this->createForm(EntityType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Save the changes made on the form
            $this->commandBus->handle($command);

            try {
                switch ($form->getClickedButton()->getName()) {
                    case 'importButton':
                        // Handle an import action based on the posted xml or import url.
                        $metadataCommand = LoadMetadataCommand::fromSaveEntityCommand($command);
                        $this->commandBus->handle($metadataCommand);
                        return $this->redirectToRoute('entity_add');
                        break;

                    case 'publishButton':
                        // Only trigger form validation on publish
                        if ($form->isValid()) {
                            die('publishing not yet supported');
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

}
