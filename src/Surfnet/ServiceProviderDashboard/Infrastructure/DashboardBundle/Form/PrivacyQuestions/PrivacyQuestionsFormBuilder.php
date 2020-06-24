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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\PrivacyQuestions;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class PrivacyQuestionsFormBuilder
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder->add(
            'whatData',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.whatData.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.whatData',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add(
            'accessData',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.accessData.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.accessData',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add(
            'country',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.country.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.country',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add(
            'securityMeasures',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.securityMeasures.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.securityMeasures',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add(
            'certification',
            CheckboxType::class,
            [
                'label' => 'privacy.form.label.certification.html',
                'required' => false,
                'attr' => ['data-help' => 'privacy.information.certification'],
            ]
        );

        $builder->add(
            'certificationLocation',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.certificationLocation.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.certificationLocation',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add(
            'certificationValidFrom',
            DateType::class,
            [
                'label' => 'privacy.form.label.certificationValidFrom.html',
                'required' => false,
                'widget' => 'single_text',
            ]
        );

        $builder->add(
            'certificationValidTo',
            DateType::class,
            [
                'label' => 'privacy.form.label.certificationValidTo.html',
                'required' => false,
                'widget' => 'single_text',
            ]
        );

        $builder->add(
            'surfmarketDpaAgreement',
            CheckboxType::class,
            [
                'label' => 'privacy.form.label.surfmarketDpaAgreement.html',
                'required' => false,
                'attr' => ['data-help' => 'privacy.information.surfmarketDpaAgreement'],
            ]
        );

        $builder->add(
            'surfnetDpaAgreement',
            CheckboxType::class,
            [
                'label' => 'privacy.form.label.surfnetDpaAgreement.html',
                'required' => false,
                'attr' => ['data-help' => 'privacy.information.surfnetDpaAgreement'],
            ]
        );

        $builder->add(
            'snDpaWhyNot',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.snDpaWhyNot.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.snDpaWhyNot',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add(
            'privacyPolicy',
            CheckboxType::class,
            [
                'label' => 'privacy.form.label.privacyPolicy.html',
                'required' => false,
                'attr' => ['data-help' => 'privacy.information.privacyPolicy'],
            ]
        );

        $builder->add(
            'privacyPolicyUrl',
            UrlType::class,
            [
                'label' => 'privacy.form.label.privacyPolicyUrl.html',
                'required' => false,
                'default_protocol' => 'https',
                'attr' => [
                    'data-parsley-type' => "url",
                    'data-help' => 'privacy.information.privacyPolicyUrl',
                ],
            ]
        );

        $builder->add(
            'otherInfo',
            TextareaType::class,
            [
                'label' => 'privacy.form.label.otherInfo.html',
                'required' => false,
                'attr' => [
                    'data-help' => 'privacy.information.otherInfo',
                    'rows' => 8,
                ],
            ]
        );

        $builder->add('save', SubmitType::class, ['attr' => ['class'=>'button']]);
    }
}
