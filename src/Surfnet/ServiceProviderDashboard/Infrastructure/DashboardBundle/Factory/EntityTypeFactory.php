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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\EntityTypeInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\OidcEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\SamlEntityType;
use Symfony\Component\Form\FormFactory;

class EntityTypeFactory
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param string $type
     * @param Service $service
     * @param string $environment
     * @param Entity|null $entity
     * @return EntityTypeInterface
     */
    public function createCreateForm($type, Service $service, $environment, Entity $entity = null)
    {
        switch (true) {
            case ($type == Entity::TYPE_OPENID_CONNECT):
            case ($type == Entity::TYPE_OPENID_CONNECT_TNG):
                $command = SaveOidcEntityCommand::forCreateAction($service);
                if ($entity) {
                    $command = SaveOidcEntityCommand::fromEntity($entity);
                }
                $command->setEnvironment($environment);
                $command->setProtocol($type);
                return $this->formFactory->create(OidcEntityType::class, $command, $this->buildOptions($environment));
            case ($type == Entity::TYPE_SAML):
                $command = SaveSamlEntityCommand::forCreateAction($service);
                if ($entity) {
                    $command = SaveSamlEntityCommand::fromEntity($entity);
                }
                $command->setEnvironment($environment);
                return $this->formFactory->create(SamlEntityType::class, $command, $this->buildOptions($environment));
        }

        throw new InvalidArgumentException("invalid form type requested: " . $type);
    }


    /**
     * @param $type
     * @param Entity $entity
     * @param $environment
     * @return EntityTypeInterface
     */
    public function createEditForm(Entity $entity)
    {
        switch (true) {
            case ($entity->getProtocol() == Entity::TYPE_OPENID_CONNECT):
            case ($entity->getProtocol() == Entity::TYPE_OPENID_CONNECT_TNG):
                $command = SaveOidcEntityCommand::fromEntity($entity);
                $command->setProtocol($entity->getProtocol());
                $command->setEnvironment($entity->getEnvironment());
                return $this->formFactory->create(OidcEntityType::class, $command, $this->buildOptions($entity->getEnvironment()));
            case ($entity->getProtocol() == Entity::TYPE_SAML):
                $command = SaveSamlEntityCommand::fromEntity($entity);
                $command->setEnvironment($entity->getEnvironment());
                return $this->formFactory->create(SamlEntityType::class, $command, $this->buildOptions($entity->getEnvironment()));
        }

        throw new InvalidArgumentException("invalid form type requested");
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
