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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;

class LoadMetadataCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var FetcherInterface
     */
    private $metadataFetcher;

    /**
     * @var ParserInterface
     */
    private $metadataParser;

    /**
     * @param EntityRepository $entityRepository
     * @param FetcherInterface $metadataFetcher
     * @param ParserInterface $parser
     */
    public function __construct(
        EntityRepository $entityRepository,
        FetcherInterface $metadataFetcher,
        ParserInterface $parser
    ) {
        $this->entityRepository = $entityRepository;
        $this->metadataFetcher = $metadataFetcher;
        $this->metadataParser = $parser;
    }

    /**
     * @param LoadMetadataCommand $command
     *
     * @throws InvalidArgumentException
     */
    public function handle(LoadMetadataCommand $command)
    {
        $entity = $this->entityRepository->findById($command->getDashboardId());

        switch (true) {
            case $command->isUrlSet():
                $url = $command->getImportUrl();

                $entity->setImportUrl($url);

                $xml = $this->metadataFetcher->fetch($url);
                break;
            case $command->isXmlSet():
                $xml = $command->getPastedMetadata();
                break;
            default:
                throw new InvalidArgumentException('Unable to load XML from the LoadMetadataCommand');
                break;
        }

        $entity->setMetadataXml($xml);

        $metadata = $this->metadataParser->parseXml($xml);

        $this->mapTextFields($entity, $metadata);
        $this->mapContacts($entity, $metadata);
        $this->mapAttributes($entity, $metadata);

        // By default set the import url as the metadataUrl but only when the metadataUrl is not set yet.
        if (!empty($entity->getMetadataUrl()) && $command->isUrlSet()) {
            $entity->setMetadataUrl($entity->getImportUrl());
        }

        $this->entityRepository->save($entity);
    }


    private function mapTextFields($entity, $metadata)
    {
        $map = [
            'acsLocation' => ['getAcsLocation', 'setAcsLocation'],
            'entityId' => ['getEntityId', 'setEntityId'],
            'logoUrl' => ['getLogoUrl', 'setLogoUrl'],
            'nameNl' => ['getNameNl', 'setNameNl'],
            'nameEn' => ['getNameEn', 'setNameEn'],
            'descriptionNl' => ['getDescriptionNl', 'setDescriptionNl'],
            'descriptionEn' => ['getDescriptionEn', 'setDescriptionEn'],
            'applicationUrlEn' => ['getApplicationUrl', 'setApplicationUrl'],
            'certificate' => ['getCertificate', 'setCertificate'],
        ];

        $this->map($map, $entity, $metadata);
    }

    private function mapAttributes(Entity $entity, Metadata $metadata)
    {
        $map = [
            'emailAddressAttribute' => ['getEmailAddressAttribute', 'setEmailAddressAttribute'],
            'displayNameAttribute' => ['getDisplayNameAttribute', 'setDisplayNameAttribute'],
            'affiliationAttribute' => ['getAffiliationAttribute', 'setAffiliationAttribute'],
            'givenNameAttribute' => ['getGivenNameAttribute', 'setGivenNameAttribute'],
            'surNameAttribute' => ['getSurNameAttribute', 'setSurNameAttribute'],
            'commonNameAttribute' => ['getCommonNameAttribute', 'setCommonNameAttribute'],
            'entitlementAttribute' => ['getEntitlementAttribute', 'setEntitlementAttribute'],
            'organizationAttribute' => ['getOrganizationAttribute', 'setOrganizationAttribute'],
            'organizationTypeAttribute' => ['getOrganizationTypeAttribute', 'setOrganizationTypeAttribute'],
            'principleNameAttribute' => ['getPrincipleNameAttribute', 'setPrincipleNameAttribute'],
            'uidAttribute' => ['getUidAttribute', 'setUidAttribute'],
            'preferredLanguageAttribute' => ['getPreferredLanguageAttribute', 'setPreferredLanguageAttribute'],
            'personalCodeAttribute' => ['getPersonalCodeAttribute', 'setPersonalCodeAttribute'],
            'eduPersonTargetedIDAttribute' => ['getEduPersonTargetedIDAttribute', 'setEduPersonTargetedIDAttribute'],
            'scopedAffiliationAttribute' => ['getScopedAffiliationAttribute', 'setScopedAffiliationAttribute'],
        ];

        $this->map($map, $entity, $metadata);
    }

    private function mapContacts(Entity $entity, Metadata $metadata)
    {
        $map = [
            'technicalContact' => ['getTechnicalContact', 'setTechnicalContact'],
            'administrativeContact' => ['getAdministrativeContact', 'setAdministrativeContact'],
            'supportContact' => ['getSupportContact', 'setSupportContact'],
        ];

        $this->map($map, $entity, $metadata);
    }

    /**
     * The map should be an associative array where the keys are the fieldnames of the metadata field. The value should
     * be an array with two values. These values being the getter and setter on the Service entity.
     *
     * Example:
     * [
     *      'myMetadataAttr' => ['getMyMetadataAttr', 'setMyMetadataAttr']
     * ]
     *
     * The map method will use this data to access and set the values on the Service entity according to the business
     * rules.
     *
     * @param array $map
     * @param Entity $entity
     * @param Metadata $metadata
     */
    private function map(array $map, Entity $entity, Metadata $metadata)
    {
        foreach ($map as $metadataFieldName => $entityMethods) {
            $setter = $entityMethods[1];
            if (!is_null($metadata->$metadataFieldName)) {
                call_user_func([$entity, $setter], $metadata->$metadataFieldName);
            }
        }
    }
}
