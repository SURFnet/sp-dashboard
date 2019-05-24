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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\AclEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AclEntityCommand $data */
        $data = $builder->getData();

        $builder->add('selected', ChoiceType::class, [
            'choices' => $data->getAvailable(),
            'choice_label' => function(IdentityProvider $idp, $key, $value) {
                return strtoupper($idp->getName());
            },
            'choice_attr' => function(IdentityProvider $idp, $key, $value) {
                return ['class' => 'category_'.strtolower($idp->getManageId())];
            },
            'expanded' => true,
            'multiple' => true,
        ])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'button']]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AclEntityCommand::class,
            'error_bubbling' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'identity_providers';
    }
}
