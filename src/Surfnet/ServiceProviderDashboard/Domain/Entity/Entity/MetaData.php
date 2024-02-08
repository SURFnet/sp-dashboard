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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngSpdClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Webmozart\Assert\Assert;
use Exception;

class MetaData implements Comparable
{
    final public const MAX_ACS_LOCATIONS = 10;

     /**
      * @SuppressWarnings(PHPMD.CyclomaticComplexity) - Due to mapping and input validation
      * @SuppressWarnings(PHPMD.NPathComplexity)      - Due to mapping and input validation
      * @throws                                       Exception
      */
    public static function fromApiResponse(array $data): self
    {
        $metaDataFields = $data['data']['metaDataFields'];
        $entityId = $data['data']['entityid'] ?? '';
        $metaDataUrl = $data['data']['metadataurl'] ?? '';
        $acsLocations = self::getAcsLocationsFromMetaDataFields($metaDataFields);
        $nameIdFormat = $metaDataFields['NameIDFormat'] ?? '';
        $certData = $metaDataFields['certData'] ?? '';
        $descriptionEn = $metaDataFields['description:en'] ?? '';
        $descriptionNl = $metaDataFields['description:nl'] ?? '';
        $nameEn = $metaDataFields['name:en'] ?? '';
        $nameNl = $metaDataFields['name:nl'] ?? '';

        Assert::string($entityId);
        Assert::string($metaDataUrl);
        Assert::allString($acsLocations);
        Assert::string($nameIdFormat);
        Assert::string($certData);
        Assert::string($descriptionEn);
        Assert::string($descriptionNl);
        Assert::string($nameEn);
        Assert::string($nameNl);

        if (count(array_unique($acsLocations)) !== count($acsLocations)) {
            throw new Exception('Double acs locations. Expected unique locations for entity: ' . $entityId);
        }

        if (count($acsLocations) > self::MAX_ACS_LOCATIONS) {
            throw new Exception('Maximum acs locations exceeded. Maximum '.self::MAX_ACS_LOCATIONS.' acs location are supported');
        }

        $contactList = ContactList::fromApiResponse($metaDataFields);
        $organization = Organization::fromApiResponse($metaDataFields);
        $coin = Coin::fromApiResponse($metaDataFields);
        $logo = Logo::fromApiResponse($metaDataFields);

        return new self(
            $entityId,
            $metaDataUrl,
            $acsLocations,
            $nameIdFormat,
            $certData,
            $descriptionEn,
            $descriptionNl,
            $nameEn,
            $nameNl,
            $contactList,
            $organization,
            $coin,
            $logo
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private ?string $entityId,
        private ?string $metaDataUrl,
        private ?array $acsLocations,
        private ?string $nameIdFormat,
        private ?string $certData,
        private ?string $descriptionEn,
        private ?string $descriptionNl,
        private ?string $nameEn,
        private ?string $nameNl,
        private readonly ContactList $contacts,
        private readonly Organization $organization,
        private readonly Coin $coin,
        private readonly Logo $logo,
    ) {
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function resetOidcNgEntitId(): void
    {
        $this->entityId = OidcngSpdClientIdParser::parse($this->entityId);
    }

    public function getMetaDataUrl(): ?string
    {
        return $this->metaDataUrl;
    }

    public function getAcsLocations(): ?array
    {
        return $this->acsLocations;
    }

    public function getNameIdFormat(): ?string
    {
        return $this->nameIdFormat;
    }

    public function getCertData(): ?string
    {
        return $this->certData;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function getDescriptionNl(): ?string
    {
        return $this->descriptionNl;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function getNameNl(): ?string
    {
        return $this->nameNl;
    }

    public function getContacts(): ContactList
    {
        return $this->contacts;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getCoin(): Coin
    {
        return $this->coin;
    }

    public function getLogo(): Logo
    {
        return $this->logo;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function merge(MetaData $metaData): void
    {
        $this->entityId = is_null($metaData->getEntityId()) ? null : $metaData->getEntityId();
        $this->metaDataUrl = is_null($metaData->getMetaDataUrl()) ? null : $metaData->getMetaDataUrl();
        $this->acsLocations = is_null($metaData->getAcsLocations()) ? null : $metaData->getAcsLocations();
        $this->nameIdFormat = is_null($metaData->getNameIdFormat()) ? null : $metaData->getNameIdFormat();
        $this->certData = is_null($metaData->getCertData()) ? null : $metaData->getCertData();
        $this->descriptionEn = is_null($metaData->getDescriptionEn()) ? null : $metaData->getDescriptionEn();
        $this->descriptionNl = is_null($metaData->getDescriptionNl()) ? null : $metaData->getDescriptionNl();
        $this->nameEn = is_null($metaData->getNameEn()) ? null : $metaData->getNameEn();
        $this->nameNl = is_null($metaData->getNameNl()) ? null : $metaData->getNameNl();
        $this->coin->merge($metaData->getCoin());
        $this->contacts->merge($metaData->getContacts());
        $this->organization->merge($metaData->getOrganization());
        $this->logo->merge($metaData->getLogo());
    }

    private static function getAcsLocationsFromMetaDataFields(array $metaDataFields): ?array
    {
        $index = 0;
        $acsLocations = [];
        while (isset($metaDataFields['AssertionConsumerService:'.$index.':Location'])) {
            if ($metaDataFields['AssertionConsumerService:'.$index.':Binding'] === Constants::BINDING_HTTP_POST) {
                $acsLocations[] = $metaDataFields['AssertionConsumerService:'.$index.':Location'];
            }
            $index++;
        }
        return $acsLocations;
    }

    private function asArrayAcsLocations(): array
    {
        $data = [];
        foreach ($this->getAcsLocations() ?? [] as $index => $location) {
            $locationIdentifier = sprintf('metaDataFields.AssertionConsumerService:%d:Location', $index);
            $bindingIdentifier = sprintf('metaDataFields.AssertionConsumerService:%d:Binding', $index);
            $data[$locationIdentifier] = $location;
            $data[$bindingIdentifier] = Constants::BINDING_HTTP_POST;
        }
        return $data;
    }

    public function asArray(): array
    {
        $data = [
            'entityid' => $this->getEntityId(),
            'metadataurl' => $this->getMetaDataUrl(),
            'metaDataFields.NameIDFormat' => $this->getNameIdFormat(),
            'metaDataFields.certData' => $this->getCertData(),
            'metaDataFields.description:nl' => $this->getDescriptionNl(),
            'metaDataFields.description:en' => $this->getDescriptionEn(),
            'metaDataFields.name:nl' => $this->getNameNl(),
            'metaDataFields.name:en' => $this->getNameEn(),
        ];

        $data += $this->asArrayAcsLocations();
        $data += $this->coin->asArray();
        $data += $this->contacts->asArray();
        $data += $this->logo->asArray();

        return $data + $this->organization->asArray();
    }
}
