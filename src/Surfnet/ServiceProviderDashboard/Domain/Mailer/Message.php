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

namespace Surfnet\ServiceProviderDashboard\Domain\Mailer;

/**
 * Implement this interface to create a message that can be sent by the Mailer.
 *
 * This interface is compatible with Swift Mailers message but only implements
 * the minimal features to send a simple mail message.
 */
interface Message
{
    public function setTo($addresses, $name = null);

    public function setFrom($addresses, $name = null);

    public function setBody($body, $contentType = null);

    public function getHeaders();
}
