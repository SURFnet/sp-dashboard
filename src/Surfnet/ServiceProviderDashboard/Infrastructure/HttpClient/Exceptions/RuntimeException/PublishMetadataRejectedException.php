<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException;

/**
 * Manage was reachable but did not accept the payload, for example because it does not match the schema of the
 * entity type. Trying the same publish again will not help.
 */
class PublishMetadataRejectedException extends PublishMetadataException
{
    /**
     * @phpstan-assert-if-true array{validations: string} $response
     */
    public static function isRejection(mixed $response): bool
    {
        return is_array($response) && isset($response['validations']) && is_string($response['validations']);
    }

    /**
     * Only the validation messages are used. Manage also echoes the payload that was sent (raw input), which
     * contains secrets and must not end up in logs.
     *
     * @param array{validations: string} $response
     */
    public static function fromManageResponse(array $response): self
    {
        return new self(sprintf('Manage rejected the payload: %s', $response['validations']));
    }
}
