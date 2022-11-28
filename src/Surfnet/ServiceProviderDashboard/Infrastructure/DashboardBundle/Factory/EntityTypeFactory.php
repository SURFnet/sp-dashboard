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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory;

use InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOauthClientCredentialClientCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OauthClientCredentialEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OidcngEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OidcngResourceServerEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\SamlEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory\SaveCommandFactoryInterface;
use Symfony\Component\Form\FormFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityTypeFactory
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var SaveCommandFactoryInterface
     */
    private $saveCommandFactory;

    /**
     * @var AttributeServiceInterface
     */
    private $attributeService;

    public function __construct(
        FormFactory $formFactory,
        SaveCommandFactoryInterface $saveCommandFactory,
        AttributeServiceInterface $attributeService
    ) {
    
        $this->formFactory = $formFactory;
        $this->saveCommandFactory = $saveCommandFactory;
        $this->attributeService = $attributeService;
    }

    public function createCreateForm(string $type, Service $service, string $environment)
    {
        switch (true) {
            case ($type === Constants::TYPE_SAML):
                $command = SaveSamlEntityCommand::forCreateAction($service);
                $command->setEnvironment($environment);
                return $this->formFactory->create(SamlEntityType::class, $command, $this->buildOptions($environment));
            case ($type === Constants::TYPE_OPENID_CONNECT_TNG):
                $command = SaveOidcngEntityCommand::forCreateAction($service);
                $command->setEnvironment($environment);
                return $this->formFactory->create(OidcngEntityType::class, $command, $this->buildOptions($environment));
            case ($type === Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER):
                $command = SaveOidcngResourceServerEntityCommand::forCreateAction($service);
                $command->setEnvironment($environment);
                return $this->formFactory->create(
                    OidcngResourceServerEntityType::class,
                    $command,
                    $this->buildOptions($environment)
                );
            case ($type === Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT):
                $command = SaveOauthClientCredentialClientCommand::forCreateAction($service);
                $command->setEnvironment($environment);
                return $this->formFactory->create(
                    OauthClientCredentialEntityType::class,
                    $command,
                    $this->buildOptions($environment)
                );
        }

        throw new InvalidArgumentException("invalid form type requested: " . $type);
    }

    public function createEditForm(ManageEntity $entity, Service $service, string $environment, $isCopy = false)
    {
        switch ($entity->getProtocol()->getProtocol()) {
            case (Constants::TYPE_SAML):
                $command = $this->saveCommandFactory->buildSamlCommandByManageEntity($entity, $environment);
                $command->setService($service);
                return $this->formFactory->create(SamlEntityType::class, $command, $this->buildOptions($environment));
            case (Constants::TYPE_OPENID_CONNECT_TNG):
                $command = $this->saveCommandFactory->buildOidcngCommandByManageEntity($entity, $environment, $isCopy);
                $command->setService($service);
                return $this->formFactory->create(OidcngEntityType::class, $command, $this->buildOptions($environment));
            case (Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER):
                $command = $this
                    ->saveCommandFactory
                    ->buildOidcngRsCommandByManageEntity(
                        $entity,
                        $environment,
                        $isCopy
                    );
                $command->setService($service);
                return $this->formFactory->create(
                    OidcngResourceServerEntityType::class,
                    $command,
                    $this->buildOptions($environment)
                );
            case (Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT):
                $command = $this->saveCommandFactory->buildOauthCccCommandByManageEntity($entity, $environment, $isCopy);
                $command->setService($service);
                return $this->formFactory->create(OauthClientCredentialEntityType::class, $command, $this->buildOptions($environment));
        }

        throw new InvalidArgumentException("invalid form type requested");
    }

    private function buildOptions($environment)
    {
        $options = [];
        if ($environment === Constants::ENVIRONMENT_PRODUCTION) {
            $options = ['validation_groups' => ['Default', 'production']];
        }
        return $options;
    }
}
