<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcngClient;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OidcngEntityType extends AbstractType
{
    /**
     * @var OidcngResourceServerOptionsFactory
     */
    private $oidcngResourceServerOptionsFactory;

    public function __construct(OidcngResourceServerOptionsFactory $oidcngResourceServerOptionsFactory)
    {
        $this->oidcngResourceServerOptionsFactory = $oidcngResourceServerOptionsFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) - for the nameIdFormat choice_attr callback parameters
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metadata = $builder->create('metadata', FormType::class, ['inherit_data' => true]);

        $metadata
            ->add(
                'clientId',
                TextType::class,
                [
                    'property_path' => 'entityId',
                    'required' => false,
                    'attr' => [
                        'data-help' => 'entity.edit.information.clientId',
                        'data-parsley-uri' => null,
                        'data-parsley-trigger' => 'blur',
                    ],
                ]
            );

        /** @var SaveOidcngEntityCommand $command */
        $command = $options['data'];
        $copy = $command->isCopy();
        $manageId = $command->getManageId();
        if (!empty($manageId) && !$copy) {
            $metadata->remove('clientId');
            $metadata
                ->add(
                    'clientId',
                    TextType::class,
                    [
                        'required' => false,
                        'validation_groups' => false,
                        'disabled' => true,
                        'attr' => [
                            'readonly' => 'readonly',
                            'data-help' => 'entity.edit.information.clientId',
                        ],
                    ]
                );
        }

        $metadata
            ->add(
                'redirectUrls',
                CollectionType::class,
                [
                    'error_bubbling' => false,
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'entry_type' => TextType::class,
                    'entry_options' => [
                        'attr' => [
                            'data-parsley-redirecturis_set' => 'true',
                            'data-parsley-redirecturis_valid' => 'true',
                            'data-parsley-trigger' => 'blur',
                        ],
                    ],
                    'attr' => [
                        'data-help' => 'entity.edit.information.redirectUrls',
                    ],
                ]
            )
            ->add(
                'accessTokenValidity',
                NumberType::class,
                [
                    'attr' => [
                        'required' => true,
                        'data-help' => 'entity.edit.information.accessTokenValidity',
                        'min' => 3600,
                        'max' => 86400,
                        'step' => 60,
                        'placeholder' => 3600,
                    ]
                ]
            )
            ->add(
                'isPublicClient',
                CheckboxType::class,
                [
                    'required' => false,
                    'attr' => [
                        'required' => false,
                        'data-help' => 'entity.edit.information.isPublicClient',
                    ]
                ]
            )
            ->add(
                'grants',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true,
                    'choices'  => OidcngClient::FORM_MANAGED_GRANTS,
                    'attr' => [
                        'data-help' => 'entity.edit.information.grantType',
                    ],
                    'choice_attr' => function () {
                        return ['class' => 'decorated'];
                    },
                ]
            )
            ->add(
                'subjectType',
                ChoiceType::class,
                [
                    'expanded' => true,
                    'multiple' => false,
                    'choices'  => [
                        'entity.edit.label.transient' => Constants::NAME_ID_FORMAT_TRANSIENT,
                        'entity.edit.label.persistent' => Constants::NAME_ID_FORMAT_PERSISTENT,
                    ],
                    'attr' => [
                        'class' => 'nameidformat-container',
                        'data-help' => 'entity.edit.information.subjectType',
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
                    'required' => true,
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
                    'required' => true,
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
                        'class' => 'requested',
                        'data-help' => 'entity.edit.information.enablePlayground',
                    ]
                ]
            );
        $builder->add($metadata);

        // Load the choices for the resource server choice form section
        $choices = $this->oidcngResourceServerOptionsFactory->build(
            $command->getService()->getTeamName(),
            $command->getEnvironment()
        );

        // If no resource servers are present, do not render the resource server section.
        if (!empty($choices)) {
            $builder
                ->add(
                    $builder->create('oidcngResourceServers', FormType::class, ['inherit_data' => true])
                        ->add(
                            'oidcngResourceServers',
                            ChoiceType::class,
                            [
                                'choices' => $choices,
                                'expanded' => true,
                                'multiple' => true,
                                'by_reference' => false,
                                'attr' => [
                                    'data-help' => 'entity.edit.information.oidcngResourceServers',
                                    'class' => 'wide'
                                ],
                                'choice_attr' => function () {
                                    return ['class' => 'decorated'];
                                },
                            ]
                        )
                );
        }

        $builder
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
                            'label' => 'entity.edit.form.attributes.oidc.givenNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.givenNameAttribute'],
                        ]
                    )
                    ->add(
                        'surNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.surNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.surNameAttribute'],
                        ]
                    )
                    ->add(
                        'commonNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.commonNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.commonNameAttribute'],
                        ]
                    )
                    ->add(
                        'displayNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.displayNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.displayNameAttribute'],
                        ]
                    )
                    ->add(
                        'emailAddressAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.emailAddressAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.emailAddressAttribute'],
                        ]
                    )
                    ->add(
                        'organizationAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.organizationAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.organizationAttribute'],
                        ]
                    )
                    ->add(
                        'organizationTypeAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.organizationTypeAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.organizationTypeAttribute'],
                        ]
                    )
                    ->add(
                        'affiliationAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.affiliationAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.affiliationAttribute'],
                        ]
                    )
                    ->add(
                        'entitlementAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.entitlementAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.entitlementAttribute'],
                        ]
                    )
                    ->add(
                        'principleNameAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.principleNameAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.principleNameAttribute'],
                        ]
                    )
                    ->add(
                        'uidAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.uidAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.uidAttribute'],
                        ]
                    )
                    ->add(
                        'preferredLanguageAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.preferredLanguageAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.preferredLanguageAttribute'],
                        ]
                    )
                    ->add(
                        'personalCodeAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.personalCodeAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.personalCodeAttribute'],
                        ]
                    )
                    ->add(
                        'scopedAffiliationAttribute',
                        AttributeType::class,
                        [
                            'label' => 'entity.edit.form.attributes.oidc.scopedAffiliationAttribute',
                            'by_reference' => false,
                            'required' => false,
                            'attr' => ['data-help' => 'entity.edit.information.oidc.scopedAffiliationAttribute'],
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

            ->add('publishButton', SubmitType::class, ['label'=> 'Publish', 'attr' => ['class' => 'button']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'button']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SaveOidcngEntityCommand::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_entity_type';
    }
}
