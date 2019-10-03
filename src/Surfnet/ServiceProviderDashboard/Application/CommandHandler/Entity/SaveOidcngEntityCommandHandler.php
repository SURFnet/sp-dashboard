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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ResourceServerCollection;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;

/**
 * Saves oidcng drafts
 */
class SaveOidcngEntityCommandHandler implements CommandHandler
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
     * @param SaveOidcngEntityCommand $command
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function handle(SaveOidcngEntityCommand $command)
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
            $secret = new Secret(Entity::OIDC_SECRET_LENGTH);
            $entity->setClientSecret($secret->getSecret());
        }

        $entity->setService($command->getService());
        $entity->setManageId($command->getManageId());
        $entity->setArchived($command->isArchived());
        $entity->setEnvironment($command->getEnvironment());
        $entity->setEntityId($command->getEntityId());
        $entity->setProtocol($command->getProtocol());
        $entity->setRedirectUris($command->getRedirectUrls());
        $entity->setAccessTokenValidity($command->getAccessTokenValidity());
        $entity->setIsPublicClient($command->isPublicClient());
        $entity->setEnablePlayground($command->isEnablePlayground());
        try {
            $grantType =  $command->getGrantType();
            $entity->setGrantType(new OidcGrantType($grantType));
        } catch (\InvalidArgumentException $e) {
            // Allow empty grant types
        }

        // The OIDC subject type is analog to the SAML NameIdFormat: https://www.pivotaltracker.com/story/show/167511146
        $entity->setNameIdFormat($command->getSubjectType());
        $entity->setLogoUrl($command->getLogoUrl());
        $entity->setNameNl($command->getNameNl());
        $entity->setNameEn($command->getNameEn());
        $entity->setDescriptionNl($command->getDescriptionNl());
        $entity->setDescriptionEn($command->getDescriptionEn());
        $entity->setApplicationUrl($command->getApplicationUrl());
        $entity->setEulaUrl($command->getEulaUrl());

        $entity->setAdministrativeContact($command->getAdministrativeContact());
        $entity->setTechnicalContact($command->getTechnicalContact());
        $entity->setSupportContact($command->getSupportContact());
        $entity->setGivenNameAttribute($command->getGivenNameAttribute());
        $entity->setSurNameAttribute($command->getSurNameAttribute());
        $entity->setCommonNameAttribute($command->getCommonNameAttribute());
        $entity->setDisplayNameAttribute($command->getDisplayNameAttribute());
        $entity->setEmailAddressAttribute($command->getEmailAddressAttribute());
        $entity->setOrganizationAttribute($command->getOrganizationAttribute());
        $entity->setOrganizationTypeAttribute($command->getOrganizationTypeAttribute());
        $entity->setAffiliationAttribute($command->getAffiliationAttribute());
        $entity->setEntitlementAttribute($command->getEntitlementAttribute());
        $entity->setPrincipleNameAttribute($command->getPrincipleNameAttribute());
        $entity->setUidAttribute($command->getUidAttribute());
        $entity->setPreferredLanguageAttribute($command->getPreferredLanguageAttribute());
        $entity->setPersonalCodeAttribute($command->getPersonalCodeAttribute());
        $entity->setScopedAffiliationAttribute($command->getScopedAffiliationAttribute());
        $entity->setComments($command->getComments());
        $entity->setOrganizationNameNl($command->getOrganizationNameNl());
        $entity->setOrganizationNameEn($command->getOrganizationNameEn());
        $entity->setOrganizationDisplayNameNl($command->getOrganizationDisplayNameNl());
        $entity->setOrganizationDisplayNameEn($command->getOrganizationDisplayNameEn());
        $entity->setOrganizationUrlNl($command->getOrganizationUrlNl());
        $entity->setOrganizationUrlEn($command->getOrganizationUrlEn());
        $entity->setOidcngResourceServers(new ResourceServerCollection($command->getOidcngResourceServers()));

        $entity->setIdpWhitelistRaw($command->getIdpWhitelistDecoded());
        $entity->setIdpAllowAll($command->getIdpAllowAll());

        $this->repository->save($entity);
    }
}
