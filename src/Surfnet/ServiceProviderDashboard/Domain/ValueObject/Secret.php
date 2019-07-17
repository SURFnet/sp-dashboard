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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

use Exception;

class Secret
{
    /**
     * @var string
     */
    private static $allowedChars = '0123456789abcdefghijklmnopqrstuvwxyz~!@#$%^&*_+=';
    private static $requiredChars = '~!@#$%^&*_+=';

    /**
     * @var string
     */
    private $secret = '';

    /**
     * @param int $length
     * @throws Exception
     */
    public function __construct($length)
    {
        if ($length < 8) {
            throw new Exception('The secret length should be a value greater then 7');
        }
        do {
            $this->secret = '';
            $nofAllowedChars = strlen(self::$allowedChars) - 1;
            for ($pos = 0; $pos < $length; $pos++) {
                $i = random_int(0, $nofAllowedChars);
                $char = self::$allowedChars[$i];
                $this->secret .= $char;
            }
        } while (!$this->isValid($this->secret));
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return bool
     */
    private function isValid($secret)
    {
        if (strpbrk($secret, self::$requiredChars) !== false) {
            return true;
        }

        return false;
    }
}
