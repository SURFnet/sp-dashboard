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

class IntakeStatusType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'service.form.label.intake_status',
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                'service.form.label.intake_status_no' => Service::INTAKE_STATUS_NO,
                'service.form.label.intake_status_yes' => Service::INTAKE_STATUS_YES,
                'service.form.label.intake_status_not_applicable' => Service::INTAKE_STATUS_NOT_APPLICABLE,
            ],
            'attr' => ['class' => 'service-status-container'],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
