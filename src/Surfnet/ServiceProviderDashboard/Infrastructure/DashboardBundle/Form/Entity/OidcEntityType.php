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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
class OidcEntityType extends AbstractType
{

    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization';
    const GRANT_TYPE_IMPLICIT = 'implicit';

    private static $responseTypes = [
        Entity::GRANT_TYPE_AUTHORIZATION_CODE_CODE => self::GRANT_TYPE_AUTHORIZATION_CODE,
        Entity::GRANT_TYPE_IMPLICIT_ID_TOKEN_TOKEN => self::GRANT_TYPE_IMPLICIT,
        Entity::GRANT_TYPE_IMPLICIT_ID_TOKEN => self::GRANT_TYPE_IMPLICIT,
    ];

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
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.ticketNumber'],
                        ]
                    )
            )
            ->add(
                $builder->create('metadata', FormType::class, ['inherit_data' => true])
                    ->add(
                        'clientId',
                        TextType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'data-help' => 'entity.edit.information.clientId',
                                'data-parsley-uri' => null,
                                'data-parsley-trigger' => 'blur',
                            ],
                        ]
                    )
                    ->add(
                        'redirectUris',
                        CollectionType::class,
                        [
                            'prototype' => true,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'required' => false,
                            'entry_type' => TextType::class,
                            'attr' => [
                                'data-help' => 'entity.edit.information.redirectUris',
                            ],
                        ]
                    )
                    ->add(
                        'grantType',
                        ChoiceType::class,
                        [
                            'expanded' => true,
                            'multiple' => false,
                            'mapped' => false,
                            'choices'  => [
                                'entity.edit.label.authorization_code' => static::GRANT_TYPE_AUTHORIZATION_CODE,
                                'entity.edit.label.implicit' => static::GRANT_TYPE_IMPLICIT,
                            ],
                            'attr' => [
                                'class' => 'grant-type-container grant-type-toggle',
                                'data-help' => 'entity.edit.information.grantType',
                            ],
                        ]
                    )
                    ->add(
                        'grantTypeResponseType',
                        ChoiceType::class,
                        [
                            'expanded' => true,
                            'multiple' => false,
                            'choices'  => [
                                'entity.edit.label.authorization_code_token' => Entity::GRANT_TYPE_AUTHORIZATION_CODE_CODE,
                                'entity.edit.label.implicit_id_token_token' => Entity::GRANT_TYPE_IMPLICIT_ID_TOKEN_TOKEN,
                                'entity.edit.label.implicit_id_token' => Entity::GRANT_TYPE_IMPLICIT_ID_TOKEN,
                            ],
                            'choice_attr' => function ($choiceValue, $key, $value) {
                                return [
                                    'data-field' => 'dashboard_bundle_entity_type_metadata_grantTypeChoice',
                                    'data-show' => self::$responseTypes[$value],
                                ];
                            },
                            'attr' => [
                                'class' => 'grant-type-response-type-container',
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
                    ->add(
                        'enablePlayground',
                        CheckboxType::class,
                        [
                            'required' => false,
                            'attr' => [
                                'class' => 'requested'
                            ]
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
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.givenNameAttribute'],
                        ]
                    )
                    ->add(
                        'surNameAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.surNameAttribute'],
                        ]
                    )
                    ->add(
                        'commonNameAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.commonNameAttribute'],
                        ]
                    )
                    ->add(
                        'displayNameAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.displayNameAttribute'],
                        ]
                    )
                    ->add(
                        'emailAddressAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.emailAddressAttribute'],
                        ]
                    )
                    ->add(
                        'organizationAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.organizationAttribute'],
                        ]
                    )
                    ->add(
                        'organizationTypeAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.organizationTypeAttribute'],
                        ]
                    )
                    ->add(
                        'affiliationAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.affiliationAttribute'],
                        ]
                    )
                    ->add(
                        'entitlementAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.entitlementAttribute'],
                        ]
                    )
                    ->add(
                        'principleNameAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.principleNameAttribute'],
                        ]
                    )
                    ->add(
                        'uidAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.uidAttribute'],
                        ]
                    )
                    ->add(
                        'preferredLanguageAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.preferredLanguageAttribute'],
                        ]
                    )
                    ->add(
                        'personalCodeAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.personalCodeAttribute'],
                        ]
                    )
                    ->add(
                        'scopedAffiliationAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.scopedAffiliationAttribute'],
                        ]
                    )
                    ->add(
                        'eduPersonTargetedIDAttribute',
                        AttributeType::class,
                        [
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.eduPersonTargetedIDAttribute'],
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
            'data_class' => SaveOidcEntityCommand::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_entity_type';
    }
}
