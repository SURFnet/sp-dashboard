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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form;

use Surfnet\ServiceProviderDashboard\Application\Command\Service\CreateServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ConnectionStatusType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ContractSignedType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\IntakeStatusType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\PrivacyQuestionAnsweredType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\RepresentativeApprovedType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service\ServiceTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('teamName')
            ->add('productionEntitiesEnabled', CheckboxType::class)
            ->add('privacyQuestionsEnabled', CheckboxType::class, ['attr' => ['class' => 'privacy-questions-toggle']])

            ->add('serviceType', ServiceTypeType::class)
            ->add('intakeStatus', IntakeStatusType::class)
            ->add('contractSigned', ContractSignedType::class)
            ->add('surfconextRepresentativeApproved', RepresentativeApprovedType::class)
            ->add('privacyQuestionsAnswered', PrivacyQuestionAnsweredType::class)
            ->add('connectionStatus', ConnectionStatusType::class)

            ->add('guid', TextType::class, ['label' => 'CRM ID'])
            ->add('save', SubmitType::class, ['attr' => ['class'=>'button']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CreateServiceCommand::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_service_type';
    }
}
