<?php

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
    const BINDING_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    // When adding valid name id formats, don't forget to add them to self::getValidNameIdFormats()
    const NAME_ID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const NAME_ID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const NAME_ID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    const ENVIRONMENT_TEST = 'test';
    const ENVIRONMENT_PRODUCTION = 'production';

    const STATE_DRAFT = 'draft';
    const STATE_PUBLISHED = 'published';
    const STATE_PUBLICATION_REQUESTED = 'requested';
    const STATE_REMOVAL_REQUESTED = 'removal requested';

    const TYPE_SAML = 'saml20';
    const TYPE_OPENID_CONNECT = 'oidc';
    const TYPE_OPENID_CONNECT_TNG = 'oidcng';
    const TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER = 'oidcng_rs';

    const OIDC_SECRET_LENGTH = 20;
}
