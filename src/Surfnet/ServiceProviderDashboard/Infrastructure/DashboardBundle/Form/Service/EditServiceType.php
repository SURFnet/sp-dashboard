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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditServiceType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder->create(
                    'general',
                    FormType::class,
                    [
                        'inherit_data' => true,
                        'label' => 'General information',
                    ]
                )
                    ->add(
                        'ticketNumber',
                        TextType::class,
                        [
                            'disabled' => true,
                            'attr' => ['help' => 'edit.service.ticketNumber'],
                        ]
                    )
                    ->add('archived')
                    ->add('environment')
                    ->add('status')
                    ->add('janusId')
            )
            ->add(
                $builder->create('metadata', FormType::class, ['inherit_data' => true])
                    ->add(
                        'importUrl',
                        TextType::class,
                        [
                            'attr' => ['help' => 'edit.service.importUrl'],
                        ]
                    )
                    ->add(
                        'metadataUrl',
                        TextType::class,
                        [
                            'attr' => ['help' => 'edit.service.metadataUrl'],
                        ]
                    )
                    ->add(
                        'metadataXml',
                        TextareaType::class,
                        [
                            'attr' => ['help' => 'edit.service.metadataXml'],
                        ]
                    )
                    ->add('acsLocation')
                    ->add('entityId')
                    ->add(
                        'certificate',
                        TextareaType::class,
                        [
                            'attr' => ['help' => 'edit.service.certificate'],
                        ]
                    )
                    ->add('logoUrl')
                    ->add('nameNl')
                    ->add(
                        'descriptionNl',
                        TextareaType::class,
                        [
                            'attr' => ['help' => 'edit.service.descriptionNl'],
                        ]
                    )
                    ->add('nameEn')
                    ->add(
                        'descriptionEn',
                        TextareaType::class,
                        [
                            'attr' => ['help' => 'edit.service.descriptionEn'],
                        ]
                    )
                    ->add('applicationUrl')
                    ->add('eulaUrl')
            )
            ->add(
                $builder->create('contactInformation', FormType::class, ['inherit_data' => true])
                    ->add(
                        'administrativeContact',
                        ContactType::class,
                        [
                            'attr' => ['help' => 'edit.service.administrativeContact'],
                        ]
                    )
                    ->add(
                        'technicalContact',
                        ContactType::class,
                        [
                            'attr' => ['help' => 'edit.service.administrativeContact'],
                        ]
                    )
                    ->add(
                        'supportContact',
                        ContactType::class,
                        [
                            'attr' => ['help' => 'edit.service.supportContact'],
                        ]
                    )
            )
            ->add(
                $builder->create('attributes', FormType::class, ['inherit_data' => true])
                    ->add(
                        'givenNameAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.givenNameAttribute'],
                        ]
                    )
                    ->add(
                        'surNameAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.surNameAttribute'],
                        ]
                    )
                    ->add(
                        'commonNameAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.commonNameAttribute'],
                        ]
                    )
                    ->add(
                        'displayNameAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.displayNameAttribute'],
                        ]
                    )
                    ->add(
                        'emailAddressAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.emailAddressAttribute'],
                        ]
                    )
                    ->add(
                        'organizationAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.organizationAttribute'],
                        ]
                    )
                    ->add(
                        'organizationTypeAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.organizationTypeAttribute'],
                        ]
                    )
                    ->add(
                        'affiliationAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.affiliationAttribute'],
                        ]
                    )
                    ->add(
                        'entitlementAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.entitlementAttribute'],
                        ]
                    )
                    ->add(
                        'principleNameAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.principleNameAttribute'],
                        ]
                    )
                    ->add(
                        'uidAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.uidAttribute'],
                        ]
                    )
                    ->add(
                        'preferredLanguageAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.preferredLanguageAttribute'],
                        ]
                    )
                    ->add(
                        'personalCodeAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.personalCodeAttribute'],
                        ]
                    )
                    ->add(
                        'scopedAffiliationAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.scopedAffiliationAttribute'],
                        ]
                    )
                    ->add(
                        'eduPersonTargetedIDAttribute',
                        AttributeType::class,
                        [
                            'attr' => ['help' => 'edit.service.eduPersonTargetedIDAttribute'],
                        ]
                    )
            )
            ->add(
                $builder->create('comments', FormType::class, ['inherit_data' => true])
                    ->add(
                        'comments',
                        TextareaType::class,
                        [
                            'attr' => ['help' => 'edit.service.comments'],
                        ]
                    )
            )
            ->add('save', SubmitType::class, ['attr' => ['class' => 'button']])
            ->add('publish', SubmitType::class, ['attr' => ['class' => 'button']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EditServiceCommand::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_edit_service_type';
    }
}
