<?php

declare(strict_types = 1);

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

class Constants
{
    final public const BINDING_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    // When adding valid name id formats, don't forget to add them to self::getValidNameIdFormats()
    final public const NAME_ID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    final public const NAME_ID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    final public const NAME_ID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    final public const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    final public const GRANT_TYPE_IMPLICIT = 'implicit';
    final public const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';

    final public const ENVIRONMENT_TEST = 'test';
    final public const ENVIRONMENT_PRODUCTION = 'production';

    final public const STATE_DRAFT = 'draft';
    final public const STATE_PUBLISHED = 'published';
    final public const STATE_PUBLICATION_REQUESTED = 'requested';
    final public const STATE_REMOVAL_REQUESTED = 'removal requested';

    final public const TYPE_SAML = 'saml20';
    final public const TYPE_OPENID_CONNECT_TNG = 'oidcng';
    final public const TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER = 'oauth20_rs';
    final public const TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT = 'oauth20_ccc';

    final public const OIDC_SECRET_LENGTH = 20;

    public static function getValidNameIdFormats(): array
    {
        return [
            Constants::NAME_ID_FORMAT_TRANSIENT,
            Constants::NAME_ID_FORMAT_PERSISTENT,
        ];
    }
}
