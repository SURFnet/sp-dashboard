<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto;

class Attribute implements AttributeInterface
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var AttributeForm
     */
    public $form;

    /**
     * @var AttributeDetail
     */
    public $detail;

    public $urns = [];

    private function __construct(
        string $id,
        array $form,
        array $detail,
        array $urns
    ) {
        $this->id = $id;
        $this->form = AttributeForm::fromForm($form);
        $this->detail = AttributeDetail::from($detail);
        $this->urns = $urns;
    }

    public static function fromAttribute(array $attribute): ?Attribute
    {
        return new self(
            $attribute['id'],
            $attribute['form'],
            $attribute['detail'],
            $attribute['urns']
        );
    }
}
