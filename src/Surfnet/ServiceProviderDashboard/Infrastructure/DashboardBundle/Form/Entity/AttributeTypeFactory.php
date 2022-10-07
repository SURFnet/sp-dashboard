<?php

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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

class AttributeTypeFactory
{
    /**
     * @var AttributeServiceInterface
     */
    private $attributeService;

    public function __construct(AttributeServiceInterface $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    public function build(FormBuilderInterface $container): FormBuilderInterface
    {
        foreach ($this->attributeService->getAttributes() as $attribute) {
            $name  = $attribute->getName();
            $container
                ->add(
                    $name,
                    AttributeType::class,
                    [
                        'label' => $attribute->getLabel(),
                        'by_reference' => false,
                        'required' => false,
                        'attr' => ['data-help' => $attribute->getInfo()],
                    ]
                );
        }

        return $container;
    }
}
