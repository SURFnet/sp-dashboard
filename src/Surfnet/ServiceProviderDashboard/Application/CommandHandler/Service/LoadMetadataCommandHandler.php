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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Service\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;

class LoadMetadataCommandHandler implements CommandHandler
{
    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    /**
     * @var FetcherInterface
     */
    private $metadataFetcher;

    /**
     * @var ParserInterface
     */
    private $metadataParser;

    /**
     * @param ServiceRepository $serviceRepository
     * @param FetcherInterface $metadataFetcher
     * @param ParserInterface $parser
     */
    public function __construct(
        ServiceRepository $serviceRepository,
        FetcherInterface $metadataFetcher,
        ParserInterface $parser
    ) {
        $this->serviceRepository = $serviceRepository;
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
        $service = $this->serviceRepository->findById($command->getServiceId());

        switch (true) {
            case $command->isUrlSet():
                $xml = $this->metadataFetcher->fetch($command->getImportUrl());
                break;
            case $command->isXmlSet():
                $xml = $command->getPastedMetadata();
                break;
            default:
                throw new InvalidArgumentException('Unable to load XML from the LoadMetadataCommand');
                break;
        }

        $service->setMetadataXml($xml);
        $service->setPastedMetadata($xml);

        $metadata = $this->metadataParser->parseXml($xml);

        $this->mapTextFields($service, $metadata);
        $this->mapContacts($service, $metadata);
        $this->mapAttributes($service, $metadata);

        $this->serviceRepository->save($service);
    }


    private function mapTextFields($service, $metadata)
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

        $this->map($map, $service, $metadata);
    }

    private function mapAttributes(Service $service, Metadata $metadata)
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

        $this->map($map, $service, $metadata);
    }

    private function mapContacts(Service $service, Metadata $metadata)
    {
        $map = [
            'technicalContact' => ['getTechnicalContact', 'setTechnicalContact'],
            'administrativeContact' => ['getAdministrativeContact', 'setAdministrativeContact'],
            'supportContact' => ['getSupportContact', 'setSupportContact'],
        ];

        $this->map($map, $service, $metadata);
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
     * @param Service $service
     * @param Metadata $metadata
     */
    private function map(array $map, Service $service, Metadata $metadata)
    {
        foreach ($map as $metadataFieldName => $serviceMethods) {
            $getter = $serviceMethods[0];
            $setter = $serviceMethods[1];
            // Only update the value on the service if the user didn't set it previously
            if (!is_null($metadata->$metadataFieldName) && empty(call_user_func([$service, $getter]))) {
                call_user_func([$service, $setter], $metadata->$metadataFieldName);
            }
        }
    }
}
