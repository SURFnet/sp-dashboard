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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\DeleteEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * The EntityControllerTrait contains the shared logic of the EntityCreateController and the EntityEditController.
 */
trait EntityControllerTrait
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
        AuthorizationService $authorizationService
    ) {
        $this->commandBus = $commandBus;
        $this->entityService = $entityService;
        $this->serviceService = $serviceService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param Request $request
     * @param SaveEntityCommand $command
     *
     * @return Form
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
            // Also show the parser messages
            $this->addFlash('preformatted', $e->getMessage());
            foreach ($e->getParserErrors() as $error) {
                $this->addFlash('preformatted', $error->message);
            }
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
        }

        $form = $this->createForm(EntityType::class, $command);

        if ($command->getStatus() === Entity::STATE_PUBLISHED) {
            $form->remove('save');
        }

        return $form;
    }

    /**
     * @param Entity $entity
     * @param FlashBagInterface $flashBag
     * @return RedirectResponse|Form
     */
    private function publishEntity(Entity $entity, FlashBagInterface $flashBag)
    {
        switch ($entity->getEnvironment()) {
            case Entity::ENVIRONMENT_TEST:
                $publishEntityCommand = new PublishEntityTestCommand($entity->getId());
                $destination = 'entity_published_test';
                break;

            case Entity::ENVIRONMENT_PRODUCTION:
                $publishEntityCommand = new PublishEntityProductionCommand($entity->getId());
                $destination = 'entity_published_production';
                break;
        }

        $this->commandBus->handle($publishEntityCommand);

        if (!$flashBag->has('error')) {
            // A clone is saved in session temporarily, to be able to report which entity was removed on the reporting
            // page we will be redirecting to in a moment.
            $this->get('session')->set('published.entity.clone', clone $entity);

            // Test entities are removed after they've been published to Manage
            $deleteCommand = new DeleteEntityCommand($entity->getId());
            $this->commandBus->handle($deleteCommand);

            return $this->redirectToRoute($destination);
        }
    }

    /**
     * Check if the form was submitted using the given button name.
     *
     * @param Form $form
     * @param string $expectedButtonName
     * @return bool
     */
    private function assertUsedSubmitButton(Form $form, $expectedButtonName)
    {
        $button = $form->getClickedButton();

        if ($button === null) {
            return false;
        }

        return $button->getName() === $expectedButtonName;
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isImportAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'importButton');
    }

    /**
     * The default action occurs when the user presses ENTER in a form.
     *
     * @param Form $form
     * @return bool
     */
    private function isDefaultAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'default');
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isSaveAction(Form $form)
    {
        if ($this->assertUsedSubmitButton($form, 'save')) {
            return true;
        }

        return $form->has('save') && $this->isDefaultAction($form);
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isPublishAction(Form $form)
    {
        if ($this->assertUsedSubmitButton($form, 'publishButton')) {
            return true;
        }

        return !$form->has('save') && $this->isDefaultAction($form);
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isCancelAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'cancel');
    }

    private function buildOptions($environment)
    {
        $options = [];
        if ($environment === Entity::ENVIRONMENT_PRODUCTION) {
            $options = ['validation_groups' => ['Default', 'production']];
        }
        return $options;
    }
}
