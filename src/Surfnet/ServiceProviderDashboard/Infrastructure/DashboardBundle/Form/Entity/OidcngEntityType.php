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
use Surfnet\ServiceProviderDashboard\Domain\Repository\TypeOfServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OidcngEntityType extends AbstractType
{
    public function __construct(
        private readonly OidcngResourceServerOptionsFactory $oidcngResourceServerOptionsFactory,
        private readonly AttributeTypeFactory $attributeTypeFactory,
        private readonly TypeOfServiceRepository $typeOfServiceProvider,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)   - for the nameIdFormat choice_attr callback parameters
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $attributesContainer = $builder->create(
            'attributes',
            FormType::class,
            [
            'inherit_data' => true,
            'attr' => ['class' => 'attributes'],
            ]
        );
        $this->buildAttributeTypes($attributesContainer);

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

        /**
 * @var SaveOidcngEntityCommand $command
*/
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
                    ],
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
                    ],
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
                    'choice_attr' => fn(): array => ['class' => 'decorated'],
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
                'isPublicInDashboard',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'entity.edit.label.isPublicOnDashboard',
                    'attr' => [
                        'required' => false,
                        'data-help' => 'entity.edit.information.isPublicOnDashboard',
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
                'typeOfService',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => $this->typeOfServiceProvider->getTypesOfServiceChoices(),
                    'choice_value' => fn(TypeOfService $tos): string => $tos->typeEn,
                    'choice_label' => fn(TypeOfService $tos): string => $tos->typeEn,
                    'choice_attr' => fn(): array => [
                        'class' => 'decorated',
                        'data-parsley-mincheck' => 1,
                        'data-parsley-maxcheck' => 3,
                    ],
                    'autocomplete' => true, // Enables the UX-Autocomplete
                    'tom_select_options' => [
                        'maxItems' => 3
                    ],
                    'multiple' => true,
                    'attr' => [
                        'class' => 'type-of-service',
                        'data-help' => 'entity.edit.information.typeOfService',
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
                    ],
                ]
            );
        $builder->add($metadata);

        // Load the choices for the resource server choice form section
        $choices = $this->oidcngResourceServerOptionsFactory->build(
            $command->getService()->getTeamName(),
            $command->getEnvironment()
        );

        // If no resource servers are present, do not render the resource server section.
        if ($choices !== []) {
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
                                    'class' => 'wide',
                                ],
                                'choice_attr' => fn(): array => ['class' => 'decorated'],
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
            ->add($attributesContainer)
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

            ->add('publishButton', SubmitType::class, ['label'=> $options['publish_button_label'], 'attr' => ['class' => 'button']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'button']]);

        // When the Oidc entity is set to have an UNSPECIFIED subject type (in manage) do not show the field on the form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var SaveOidcngEntityCommand $data */
            $data = $event->getData();
            if ($data->getSubjectType() === Constants::NAME_ID_FORMAT_UNSPECIFIED) {
                $form = $event->getForm();
                if ($form->has('metadata') && $form->get('metadata')->has('subjectType')) {
                    $form->get('metadata')->remove('subjectType');
                }
            }
        });
    }

    private function buildAttributeTypes(FormBuilderInterface $container): FormBuilderInterface
    {
        return $this->attributeTypeFactory->build($container, Constants::TYPE_OPENID_CONNECT_TNG);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SaveOidcngEntityCommand::class, 'publish_button_label' => 'entity.add.label.publish']);
    }

    public function getBlockPrefix(): string
    {
        return 'dashboard_bundle_entity_type';
    }
}
