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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'firstName',
            TextType::class,
            [
                'required' => false,
            ]
        );

        $builder->add(
            'lastName',
            TextType::class,
            [
                'required' => false,
            ]
        );

        $builder->add(
            'email',
            EmailType::class,
            [
                'required' => false,
                'attr' => [
                    'data-parsley-trigger' => 'blur',
                    'placeholder' => 'entity.edit.contact_information.' . $builder->getName().'.email.placeholder',
                ],
            ]
        );

        $builder->add(
            'phone',
            TextType::class,
            [
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Contact::class,
            'error_bubbling' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'dashboard_bundle_contact_type';
    }
}
