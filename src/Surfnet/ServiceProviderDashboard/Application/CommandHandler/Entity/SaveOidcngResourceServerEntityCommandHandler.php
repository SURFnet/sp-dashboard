<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;

/**
 * Saves oidcng drafts
 */
class SaveOidcngResourceServerEntityCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param SaveOidcngResourceServerEntityCommand $command
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function handle(SaveOidcngResourceServerEntityCommand $command)
    {
        // If the entity does not exist yet, create it on the fly
        if (is_null($command->getId())) {
            $id = Uuid::uuid1()->toString();
            if (!$this->repository->isUnique($id)) {
                throw new InvalidArgumentException(
                    'The id that was generated for the entity was not unique, please try again'
                );
            }

            $entity = new Entity();
            $entity->setId($id);
            $entity->setService($command->getService());
            $command->setId($id);

            if (empty($command->getManageId())) {
                $secret = new Secret(20);
                $entity->setClientSecret($secret->getSecret());
            }
        } else {
            $entity = $this->repository->findById($command->getId());
        }

        if (is_null($entity)) {
            throw new EntityNotFoundException('The requested entity cannot be found');
        }

        if (!$command->getManageId()) {
            $secret = new Secret(Constants::OIDC_SECRET_LENGTH);
            $entity->setClientSecret($secret->getSecret());
        }

        $entity->setService($command->getService());
        $entity->setManageId($command->getManageId());
        $entity->setArchived($command->isArchived());
        $entity->setEnvironment($command->getEnvironment());
        $entity->setEntityId($command->getEntityId());
        $entity->setProtocol($command->getProtocol());
        $entity->setNameIdFormat(Constants::NAME_ID_FORMAT_PERSISTENT);
        $entity->setNameNl($command->getNameNl());
        $entity->setNameEn($command->getNameEn());
        $entity->setDescriptionNl($command->getDescriptionNl());
        $entity->setDescriptionEn($command->getDescriptionEn());

        $entity->setAdministrativeContact($command->getAdministrativeContact());
        $entity->setTechnicalContact($command->getTechnicalContact());
        $entity->setSupportContact($command->getSupportContact());

        // OrganizationName is tracked on the Service
        $entity->setOrganizationNameNl($entity->getService()->getOrganizationNameNl());
        $entity->setOrganizationNameEn($entity->getService()->getOrganizationNameEn());

        $entity->setComments($command->getComments());

        $this->repository->save($entity);
    }
}
