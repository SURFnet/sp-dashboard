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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create(
                    'general',
                    FormType::class,
                    [
                        'inherit_data' => true,
                        'label' => 'General information'
                    ]
                )
                    ->add('name')
                    ->add(
                        'organizationNameNl',
                        TextType::class,
                        [
                            'required' => true,
                            'label' => 'service.form.label.organization_name_nl',
                        ]
                    )
                    ->add(
                        'organizationNameEn',
                        TextType::class,
                        [
                            'required' => true,
                            'label' => 'service.form.label.organization_name_en',
                        ]
                    )
                    ->add(
                        'institutionId',
                        TextType::class,
                        [
                            'required' => false,
                            'label' => 'service.form.label.institution_id',
                            'attr' => ['class' => 'institution-id-container']
                        ]
                    )
                    ->add(
                        'productionEntitiesEnabled',
                        CheckboxType::class,
                        [
                            'required' => false,
                        ]
                    )
                    ->add(
                        'privacyQuestionsEnabled',
                        CheckboxType::class,
                        [
                            'required' => false,
                            'attr' => ['class' => 'privacy-questions-toggle'],
                        ]
                    )
                    ->add(
                        'clientCredentialClientsEnabled',
                        CheckboxType::class,
                        [
                            'required' => false,
                        ]
                    )
                    ->add('guid', TextType::class, ['label' => 'CRM ID'])
            )
            ->add(
                $builder->create(
                    'serviceStatus',
                    FormType::class,
                    [
                        'inherit_data' => true,
                        'label' => 'Status indicators'
                    ]
                )
                    ->add('serviceType', ServiceTypeType::class)
                    ->add('intakeStatus', IntakeStatusType::class)
                    ->add('contractSigned', ContractSignedType::class)
                    ->add('surfconextRepresentativeApproved', RepresentativeApprovedType::class)
            )
            ->add(
                $builder->create(
                    'teams',
                    FormType::class,
                    [
                        'inherit_data' => true,
                        'label' => 'Teams'
                    ]
                )
                ->add('teamName', null, [
                    'label' => 'Team identifier',
                ])
            )
        ->add('save', SubmitType::class, ['attr' => ['class' => 'button']])
        ->add('delete', SubmitType::class, ['attr' => ['class' => 'button btn-danger']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            ['data_class' => EditServiceCommand::class]
        );
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_edit_service_type';
    }
}
