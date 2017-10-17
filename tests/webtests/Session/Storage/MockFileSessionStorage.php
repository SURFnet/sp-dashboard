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

namespace Surfnet\ServiceProviderDashboard\Webtests\Session\Storage;

use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage as CoreMockFileSessionStorage;

/**
 * Session storage for webtests.
 *
 * This session storage is based on Symfony filesystem storage backend.
 *
 * The example documented here: https://symfony.com/doc/current/testing/http_authentication.html
 * does not work becuase AbstractTestSessionListener sets the ID on kernel
 * boot after and the save() method in the WebTestCase also sets the session
 * ID. The file storage does not allow setting the ID twice so we override it
 * here to allow for it.
 */
class MockFileSessionStorage extends CoreMockFileSessionStorage
{
    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        if ($this->id !== $id) {
            parent::setId($id);
        }
    }
}
