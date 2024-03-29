<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity;

use \Surfnet\ServiceProviderDashboard\Application\Service\AttributeServiceInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Symfony\Component\Form\FormBuilderInterface;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Attribute;

class AttributeTypeFactory
{
    public function __construct(private readonly AttributeServiceInterface $attributeService)
    {
    }

    public function build(FormBuilderInterface $container, string $entityType): FormBuilderInterface
    {
        foreach ($this->attributeService->getAttributeTypeAttributes() as $attribute) {
            if ($attribute->isExcluded($entityType)) {
                continue;
            }
            $container
                ->add(
                    $attribute->getName(),
                    AttributeType::class,
                    [
                        'label' => $this->mapEntityToLabel($attribute, $entityType),
                        'by_reference' => false,
                        'required' => false,
                        'attr' => ['data-help' => $this->mapEntityToInfo($attribute, $entityType)],
                    ]
                );
        }

        return $container;
    }

    private function mapEntityToLabel(Attribute $attribute, string $type): string
    {
        return match ($type) {
            Constants::TYPE_SAML => $attribute->getSaml20Label(),
            Constants::TYPE_OPENID_CONNECT_TNG => $attribute->getOidcngLabel(),
            default => '',
        };
    }

    private function mapEntityToInfo(Attribute $attribute, string $type): string
    {
        return match ($type) {
            Constants::TYPE_SAML => $attribute->getSaml20Info(),
            Constants::TYPE_OPENID_CONNECT_TNG => $attribute->getOidcngInfo(),
            default => '',
        };
    }
}
