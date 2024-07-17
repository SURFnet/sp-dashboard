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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Session;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\AuthenticatedSessionStateHandler;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Value\DateTime;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Value\TimeFrame;

class SessionLifetimeGuard
{
    /**
     * @var TimeFrame
     */
    private $relativeTimeoutLimit;
    /**
     * @var TimeFrame
     */
    private $absoluteTimeoutLimit;

    public function __construct(TimeFrame $absoluteTimeoutLimit, TimeFrame $relativeTimeoutLimit)
    {
        $this->absoluteTimeoutLimit = $absoluteTimeoutLimit;
        $this->relativeTimeoutLimit = $relativeTimeoutLimit;
    }

    /**
     * @param AuthenticatedSessionStateHandler $sessionStateHandler
     * @return bool
     */
    public function sessionLifetimeWithinLimits(AuthenticatedSessionStateHandler $sessionStateHandler)
    {
        return $this->sessionLifetimeWithinAbsoluteLimit($sessionStateHandler)
                && $this->sessionLifetimeWithinRelativeLimit($sessionStateHandler);
    }

    /**
     * @param AuthenticatedSessionStateHandler $sessionStateHandler
     * @return bool
     */
    public function sessionLifetimeWithinAbsoluteLimit(AuthenticatedSessionStateHandler $sessionStateHandler)
    {
        if (!$sessionStateHandler->isAuthenticationMomentLogged()) {
            return true;
        }

        $authenticationMoment = $sessionStateHandler->getAuthenticationMoment();
        $sessionTimeoutMoment = $this->absoluteTimeoutLimit->getEndWhenStartingAt($authenticationMoment);
        $now = DateTime::now();

        if ($now->comesBeforeOrIsEqual($sessionTimeoutMoment)) {
            return true;
        }

        return false;
    }

    /**
     * @param AuthenticatedSessionStateHandler $sessionStateHandler
     * @return bool
     */
    public function sessionLifetimeWithinRelativeLimit(AuthenticatedSessionStateHandler $sessionStateHandler)
    {
        if (!$sessionStateHandler->hasSeenInteraction()) {
            return true;
        }

        $lastInteractionMoment = $sessionStateHandler->getLastInteractionMoment();
        $sessionTimeoutMoment = $this->relativeTimeoutLimit->getEndWhenStartingAt($lastInteractionMoment);
        $now = DateTime::now();

        if ($now->comesBeforeOrIsEqual($sessionTimeoutMoment)) {
            return true;
        }

        return false;
    }
}
