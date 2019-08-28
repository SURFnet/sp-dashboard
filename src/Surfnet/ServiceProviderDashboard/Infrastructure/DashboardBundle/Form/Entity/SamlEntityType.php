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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamlEntityType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) - for the nameIdFormat choice_attr callback parameters
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // The first button in a form defines the default behaviour when
            // submitting the form by pressing ENTER. We add a 'default
            // action' button on top of the form so the controller action
            // handling the form submission can choose what action to
            // perform. This is to prevent the import action when submitting
            // the form (the import button is now the second button on the
            // form).
            ->add('default', SubmitType::class, ['attr' => ['style' => 'display: none']])
            ->add(
                $builder->create('metadata', FormType::class, ['inherit_data' => true])
                    ->add(
                        'importUrl',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.importUrl',
                                'data-parsley-urlstrict' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'pastedMetadata',
                        TextareaType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.pastedMetadata',
                                'rows' => 10,
                            ],
                        ]
                    )
                    ->add(
                        'importButton',
                        SubmitType::class,
                        [
                            'label' => 'Import',
                            'attr' => ['class' => 'button'],
                        ]
                    )
                    ->add(
                        'metadataUrl',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.metadataUrl',
                                'data-parsley-urlstrict' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'acsLocation',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.acsLocation',
                                'data-parsley-urlstrict' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'entityId',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.entityId',
                                'data-parsley-uri' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'nameIdFormat',
                        ChoiceType::class,
                        [
                            'expanded' => true,
                            'multiple' => false,
                            'choices'  => [
                                'entity.edit.label.transient' => Entity::NAME_ID_FORMAT_TRANSIENT,
                                'entity.edit.label.persistent' => Entity::NAME_ID_FORMAT_PERSISTENT,
                            ],
                            'attr' => [
                                'class' => 'nameidformat-container',
                                'data-help' => 'entity.edit.information.nameIdFormat',
                            ],
                        ]
                    )
                    ->add(
                        'certificate',
                        TextareaType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.certificate',
                                'rows' => 10,
                            ],
                        ]
                    )
                    ->add(
                        'logoUrl',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.logoUrl',
                                'data-parsley-urlstrict' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'nameNl',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.nameNl'],
                        ]
                    )
                    ->add(
                        'descriptionNl',
                        TextareaType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.descriptionNl',
                                'rows' => 10,
                            ],
                        ]
                    )
                    ->add(
                        'nameEn',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.nameEn'],
                        ]
                    )
                    ->add(
                        'descriptionEn',
                        TextareaType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.descriptionEn',
                                'rows' => 10,
                            ],
                        ]
                    )
                    ->add(
                        'applicationUrl',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.applicationUrl',
                                'data-parsley-urlstrict' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'eulaUrl',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.eulaUrl',
                                'data-parsley-urlstrict' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
            )
            ->add(
                $builder->create('contactInformation', FormType::class, ['inherit_data' => true])
                    ->add(
                        'administrativeContact',
                        ContactType::class,
                        [
                            'by_reference' => false,
                            'attr' => ['data-help' => 'entity.edit.information.administrativeContact'],
                        ]
                    )
                    ->add(
                        'technicalContact',
                        ContactType::class,
                        [
                            'by_reference' => false,
                            'attr' => ['data-help' => 'entity.edit.information.technicalContact'],
                        ]
                    )
                    ->add(
                        'supportContact',
                        ContactType::class,
                        [
                            'by_reference' => false,
                            'attr' => ['data-help' => 'entity.edit.information.supportContact'],
                        ]
                    )
            )
            ->add(
                $builder->create('attributes', FormType::class, [
                    'inherit_data' => true,
                    'attr' => ['class' => 'attributes']
                ])
                    ->add(
                        'givenNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.givenNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.givenNameAttribute'],
                        ]
                    )
                    ->add(
                        'surNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.surNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.surNameAttribute'],
                        ]
                    )
                    ->add(
                        'commonNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.commonNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.commonNameAttribute'],
                        ]
                    )
                    ->add(
                        'displayNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.displayNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.displayNameAttribute'],
                        ]
                    )
                    ->add(
                        'emailAddressAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.emailAddressAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.emailAddressAttribute'],
                        ]
                    )
                    ->add(
                        'organizationAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.organizationAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.organizationAttribute'],
                        ]
                    )
                    ->add(
                        'organizationTypeAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.organizationTypeAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.organizationTypeAttribute'],
                        ]
                    )
                    ->add(
                        'affiliationAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.affiliationAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.affiliationAttribute'],
                        ]
                    )
                    ->add(
                        'entitlementAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.entitlementAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.entitlementAttribute'],
                        ]
                    )
                    ->add(
                        'principleNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.principleNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.principleNameAttribute'],
                        ]
                    )
                    ->add(
                        'uidAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.uidAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.uidAttribute'],
                        ]
                    )
                    ->add(
                        'preferredLanguageAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.preferredLanguageAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.preferredLanguageAttribute'],
                        ]
                    )
                    ->add(
                        'personalCodeAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.personalCodeAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.personalCodeAttribute'],
                        ]
                    )
                    ->add(
                        'scopedAffiliationAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.scopedAffiliationAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.scopedAffiliationAttribute'],
                        ]
                    )
                    ->add(
                        'eduPersonTargetedIDAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.saml20.eduPersonTargetedIDAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.saml20.eduPersonTargetedIDAttribute'],
                        ]
                    )
            )
            ->add(
                $builder->create('comments', FormType::class, ['inherit_data' => true])
                    ->add(
                        'comments',
                        TextareaType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.comments',
                                'rows' => 10,
                            ],
                        ]
                    )
            )

            ->add('status', HiddenType::class)
            ->add('manageId', HiddenType::class)
            ->add('environment', HiddenType::class)
            ->add('organizationNameNl', HiddenType::class)
            ->add('organizationNameEn', HiddenType::class)
            ->add('organizationDisplayNameNl', HiddenType::class)
            ->add('organizationDisplayNameEn', HiddenType::class)
            ->add('organizationUrlNl', HiddenType::class)
            ->add('organizationUrlEn', HiddenType::class)

            ->add('save', SubmitType::class, ['attr' => ['class' => 'button']])
            ->add('publishButton', SubmitType::class, ['label'=> 'Publish', 'attr' => ['class' => 'button']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'button']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SaveSamlEntityCommand::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_entity_type';
    }
}
