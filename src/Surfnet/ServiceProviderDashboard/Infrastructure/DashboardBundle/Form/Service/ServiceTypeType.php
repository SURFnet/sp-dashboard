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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'service.form.label.service_type',
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                'service.form.label.service_type_institute' => Service::SERVICE_TYPE_INSTITUTE,
                'service.form.label.service_type_non_institute' => Service::SERVICE_TYPE_NON_INSTITUTE,
            ],
            'attr' => ['class' => 'service-status-container contract-signed-toggle'],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
