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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Entity\ChooseEntityTypeCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseEntityTypeType extends AbstractType
{
    private $formCount = 0;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formCount++;

        /** @var ChooseEntityTypeCommand $command */
        $command = $builder->getData();
        $choices = $command->getProtocolChoices();

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $choices,
                'choice_translation_domain' => true,
                'label' => false,
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ChooseEntityTypeCommand::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_choose_entity_type_' . $this->formCount;
    }
}
