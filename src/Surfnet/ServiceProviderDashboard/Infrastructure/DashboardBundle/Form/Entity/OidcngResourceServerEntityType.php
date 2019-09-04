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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Symfony\Component\Form\AbstractType;
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
class OidcngResourceServerEntityType extends AbstractType
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
                    ],
                ]
            );

        $manageId = $options['data']->getManageId();
        if (!empty($manageId)) {
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
            );

        $builder
            ->add($metadata)
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
            );
        $builder
            ->add(
                $builder->create(
                    'comments',
                    FormType::class,
                    ['inherit_data' => true, 'attr' => ['class' => 'attributes']]
                )
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
            'data_class' => SaveOidcngResourceServerEntityCommand::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_entity_type';
    }
}
