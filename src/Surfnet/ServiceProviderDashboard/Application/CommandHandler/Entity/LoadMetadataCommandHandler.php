<?php

//declare(strict_types = 1);

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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeNameServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Exception\AttributeNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;

class LoadMetadataCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly AttributeNameServiceInterface $attributeNameService,
        private readonly FetcherInterface $metadataFetcher,
        private readonly ParserInterface $metadataParser
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(LoadMetadataCommand $command): void
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
        }

        $metadata = $this->metadataParser->parseXml($xml);

        $this->mapTextFields($targetCommand, $metadata);
        $this->mapContacts($targetCommand, $metadata);
        $this->mapAttributes($targetCommand, $metadata);

        // By default set the import url as the metadataUrl but only when the metadataUrl is not set yet.
        if (($targetCommand->getMetadataUrl() === null || $targetCommand->getMetadataUrl() === '' || $targetCommand->getMetadataUrl() === '0') && $command->isUrlSet()) {
            $targetCommand->setMetadataUrl($targetCommand->getImportUrl());
        }

        $command->setNameIdFormat($metadata->nameIdFormat);
    }

    private function mapTextFields(SaveSamlEntityCommand $command, \Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata $metadata): void
    {
        $map = [
            'acsLocations' => ['getAcsLocations', 'setAcsLocations'],
            'entityId' => ['getEntityId', 'setEntityId'],
            'logoUrl' => ['getLogoUrl', 'setLogoUrl'],
            'nameNl' => ['getNameNl', 'setNameNl'],
            'nameEn' => ['getNameEn', 'setNameEn'],
            'descriptionNl' => ['getDescriptionNl', 'setDescriptionNl'],
            'descriptionEn' => ['getDescriptionEn', 'setDescriptionEn'],
            'applicationUrlEn' => ['getApplicationUrl', 'setApplicationUrl'],
            'certificate' => ['getCertificate', 'setCertificate'],
        ];

        $this->map($map, $command, $metadata);
    }

    private function mapAttributes(SaveSamlEntityCommand $command, Metadata $metadata): void
    {
        $attributeNames = $this->attributeNameService->getAttributeTypeNames();

        foreach ($attributeNames as $attributeName) {
            try {
                $command->setAttribute($attributeName, $metadata->getAttribute($attributeName));
            } catch (AttributeNotFoundException) {
                // just continue, apparently attribute is not available at the metadata
            }
        }
    }

    private function mapContacts(SaveSamlEntityCommand $command, Metadata $metadata): void
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
     * be an array with two values. These values being the getter and setter on the SaveSamlEntityCommand, effectively
     * updating the form fields.
     *
     * Example:
     * [
     *      'myMetadataAttr' => ['getMyMetadataAttr', 'setMyMetadataAttr']
     * ]
     */
    private function map(array $map, SaveSamlEntityCommand $command, Metadata $metadata): void
    {
        foreach ($map as $metadataFieldName => $entityMethods) {
            $setter = $entityMethods[1];
            if (!is_null($metadata->$metadataFieldName)) {
                call_user_func([$command, $setter], $metadata->$metadataFieldName);
            }
        }
    }
}
