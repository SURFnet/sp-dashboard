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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;

class LoadMetadataCommandHandler implements CommandHandler
{
    /**
     * @var FetcherInterface
     */
    private $metadataFetcher;

    /**
     * @var ParserInterface
     */
    private $metadataParser;

    /**
     * @param FetcherInterface $metadataFetcher
     * @param ParserInterface $parser
     */
    public function __construct(
        FetcherInterface $metadataFetcher,
        ParserInterface $parser
    ) {
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
        $targetCommand = $command->getSaveEntityCommand();

        switch (true) {
            case $command->isUrlSet():
                $url = $command->getImportUrl();

                $targetCommand->setImportUrl($url);

                $xml = $this->metadataFetcher->fetch($url);
                break;
            case $command->isXmlSet():
                $xml = $command->getPastedMetadata();
                break;
            default:
                throw new InvalidArgumentException('Unable to load XML from the LoadMetadataCommand');
                break;
        }

        $targetCommand->setPastedMetadata($xml);

        $metadata = $this->metadataParser->parseXml($xml);

        $this->mapTextFields($targetCommand, $metadata);
        $this->mapContacts($targetCommand, $metadata);
        $this->mapAttributes($targetCommand, $metadata);

        // By default set the import url as the metadataUrl but only when the metadataUrl is not set yet.
        if (empty($targetCommand->getMetadataUrl()) && $command->isUrlSet()) {
            $targetCommand->setMetadataUrl($targetCommand->getImportUrl());
        }

        $command->setNameIdFormat($metadata->nameIdFormat);
    }


    private function mapTextFields(SaveEntityCommand $command, $metadata)
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
            'organizationNameEn' => ['getOrganizationNameEn', 'setOrganizationNameEn'],
            'organizationDisplayNameEn' => ['getOrganizationDisplayNameEn', 'setOrganizationDisplayNameEn'],
            'organizationUrlEn' => ['getOrganizationUrlEn', 'setOrganizationUrlEn'],
            'organizationNameNl' => ['getOrganizationNameNl', 'setOrganizationNameNl'],
            'organizationDisplayNameNl' => ['getOrganizationDisplayNameNl', 'setOrganizationDisplayNameNl'],
            'organizationUrlNl' => ['getOrganizationUrlNl', 'setOrganizationUrlNl'],
        ];

        $this->map($map, $command, $metadata);
    }

    private function mapAttributes(SaveEntityCommand $command, Metadata $metadata)
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

        $this->map($map, $command, $metadata);
    }

    private function mapContacts(SaveEntityCommand $command, Metadata $metadata)
    {
        $map = [
            'technicalContact' => ['getTechnicalContact', 'setTechnicalContact'],
            'administrativeContact' => ['getAdministrativeContact', 'setAdministrativeContact'],
            'supportContact' => ['getSupportContact', 'setSupportContact'],
        ];

        $this->map($map, $command, $metadata);
    }

    /**
     * The map should be an associative array where the keys are the fieldnames of the metadata field. The value should
     * be an array with two values. These values being the getter and setter on the SaveEntityCommand, effectively
     * updating the form fields.
     *
     * Example:
     * [
     *      'myMetadataAttr' => ['getMyMetadataAttr', 'setMyMetadataAttr']
     * ]
     *
     * @param array $map
     * @param SaveEntityCommand $command
     * @param Metadata $metadata
     */
    private function map(array $map, SaveEntityCommand $command, Metadata $metadata)
    {
        foreach ($map as $metadataFieldName => $entityMethods) {
            $setter = $entityMethods[1];
            if (!is_null($metadata->$metadataFieldName)) {
                call_user_func([$command, $setter], $metadata->$metadataFieldName);
            }
        }
    }
}
