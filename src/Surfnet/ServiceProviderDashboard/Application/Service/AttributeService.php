<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Application\Exception\AttributeNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Service\ValueObject\EntityMergeAttribute;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Attribute;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityDetailAttribute;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto\Attribute as AttributeDto;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepositoryInterface;

class AttributeService implements AttributeServiceInterface
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    private $attributes = [];

    /**
     * @var string
     */
    private $language;

    public function __construct(AttributeRepositoryInterface $attributeRepository, $language)
    {
        $this->attributeRepository = $attributeRepository;
        $this->language = $language;
    }

    public function getAttributeTypeAttributes(): array
    {
        if (empty($this->attributes)) {
            $attributes = $this->attributeRepository->findAll();

            foreach ($attributes ?? [] as $value) {
                $this->attributes[$value->id] = Attribute::fromAttribute(
                    $value,
                    $value->translations[$this->language]
                );
            }
        }
        return $this->attributes;
    }

    /**
     * @return EntityMergeAttribute[]
     */
    public function getEntityMergeAttributes(): array
    {
        $entityMergeAttributes = [];
        $attributes = $this->getAttributeTypeAttributes();
        foreach ($attributes ?? [] as $attribute) {
            $entityMergeAttributes[] = EntityMergeAttribute::fromAttribute(
                $attribute->getName(),
                $attribute->getUrns()[0]
            );
        }
        return $entityMergeAttributes;
    }

    public function getUrns(): array
    {
        $urns = [];
        $attributes = $this->getAttributeTypeAttributes();
        foreach ($attributes ?? [] as $attribute) {
            $urns[] = $attribute->getUrns()[0];
        }
        return $urns;
    }

    public function createEntityDetailAttributes(
        AttributeList $manageAttributes,
        string $entityType
    ): array {
        $attributes = [];
        foreach ($manageAttributes->getAttributes() as $attribute) {
            $attributeDto = $this->attributeRepository->findOneByName($attribute[0]->getName());
            if ($attributeDto instanceof AttributeDto) {
                $viewObject = new EntityDetailAttribute();
                $viewObject->value = $attribute[0]->getMotivation();
                $viewObject->informationPopup = $this->getInfoFromAttributeDto($attributeDto, $entityType);
                $viewObject->label = $this->getLabelFromAttributeDto($attributeDto, $entityType);
                $viewObject->excludedFor = $attributeDto->excludeOnEntityType;
                $attributes[] = $viewObject;
            }
        }
        return $attributes;
    }

    public function getAttributeFriendlyName(string $identifier, string $type): string
    {
        $attributes = $this->getAttributeTypeAttributes();
        foreach ($attributes ?? [] as $attribute) {
            if ($identifier === $attribute->getName()) {
                return $this->getLabelFromAttribute($attribute, $type);
            }
        }
        throw new AttributeNotFoundException(sprintf('Unable to find attribute with identifier: %s', $identifier));
    }

    private function getLabelFromAttribute(
        Attribute $attribute,
        string $entityType
    ) {
        switch ($entityType) {
            case Constants::TYPE_SAML:
                return $attribute->getSaml20Label();
            case Constants::TYPE_OPENID_CONNECT_TNG:
                return $attribute->getOidcngLabel();
            default:
                return '';
        }
    }

    private function getInfoFromAttributeDto(
        AttributeDto $attributeDto,
        string $entityType
    ) {
        switch ($entityType) {
            case Constants::TYPE_SAML:
                return $attributeDto->translations[$this->language]->saml20Info;
            case Constants::TYPE_OPENID_CONNECT_TNG:
                return $attributeDto->translations[$this->language]->oidcngInfo;
            default:
                return '';
        }
    }

    private function getLabelFromAttributeDto(
        AttributeDto $attributeDto,
        string $entityType
    ) {
        switch ($entityType) {
            case Constants::TYPE_SAML:
                return $attributeDto->translations[$this->language]->saml20Label;
            case Constants::TYPE_OPENID_CONNECT_TNG:
                return $attributeDto->translations[$this->language]->oidcngLabel;
            default:
                return '';
        }
    }
}
