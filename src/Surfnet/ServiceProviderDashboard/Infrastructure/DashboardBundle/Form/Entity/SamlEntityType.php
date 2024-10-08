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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\TypeOfServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfService;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SamlEntityType extends AbstractType
{
    public function __construct(
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

        $builder
            // The first button in a form defines the default behaviour when
            // submitting the form by pressing ENTER. We add a 'default
            // action' button on top of the form so the controller action
            // handling the form submission can choose what action to
            // perform. This is to prevent the import action when submitting
            // the form (the import button is now the second button on the
            // form).
            ->add('default', SubmitType::class, ['attr' => ['class' => 'hidden']])
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
                        'acsLocations',
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
                                    'data-parsley-urlstrict' => null,
                                ],
                            ],
                            'attr' => [
                                'data-help' => 'entity.edit.information.acsLocation',
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
                                'entity.edit.label.transient' => Constants::NAME_ID_FORMAT_TRANSIENT,
                                'entity.edit.label.persistent' => Constants::NAME_ID_FORMAT_PERSISTENT,
                            ],
                            'attr' => [
                                'class' => 'nameidformat-attributesContainer',
                                'data-help' => 'entity.edit.information.nameIdFormat',
                            ],
                        ]
                    )
                    ->add(
                        'isPublicInDashboard',
                        CheckboxType::class,
                        [
                            'label' => 'entity.edit.label.isPublicOnDashboard',
                            'required' => false,
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

        // When the SAML2.0 entity is set to have an UNSPECIFIED name id format (in manage) do not show the field on the form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var SaveSamlEntityCommand $data */
            $data = $event->getData();
            if ($data->getNameIdFormat() === Constants::NAME_ID_FORMAT_UNSPECIFIED) {
                $form = $event->getForm();
                if ($form->has('metadata') && $form->get('metadata')->has('nameIdFormat')) {
                    $form->get('metadata')->remove('nameIdFormat');
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SaveSamlEntityCommand::class, 'publish_button_label' => 'entity.add.label.publish']);
    }

    public function getBlockPrefix(): string
    {
        return 'dashboard_bundle_entity_type';
    }

    private function buildAttributeTypes(FormBuilderInterface $container): FormBuilderInterface
    {
        return $this->attributeTypeFactory->build($container, Constants::TYPE_SAML);
    }
}
