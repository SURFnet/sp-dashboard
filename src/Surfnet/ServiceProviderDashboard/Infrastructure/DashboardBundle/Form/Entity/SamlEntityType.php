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
use Symfony\Component\Form\AbstractType;
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
class SamlEntityType extends AbstractType
{
    public function __construct(private readonly AttributeTypeFactory $attributeTypeFactory)
    {
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
