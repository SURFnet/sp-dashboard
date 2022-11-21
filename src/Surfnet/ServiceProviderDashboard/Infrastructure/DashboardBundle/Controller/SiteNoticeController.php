<?php

/**
 * Copyright 2021 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SiteNoticeController extends AbstractController
{
    /**
     * @var string
     */
    private $noticeDate;

    public function __construct(string $noticeDate)
    {
        $this->noticeDate = $noticeDate;
    }

    /**
     * Show the Global Site Notice
     *
     * In this notification, any message the admins wish for the
     * users to see is passed on.  Users can close the notification.
     * This desire to not see the notification is stored both in the
     * session and in cookies.
     */
    public function showGlobalSiteNoticeAction(Request $request)
    {
        $cookieString = str_replace('.', '_', 'site_notice.closed.' . $this->noticeDate);
        $cookie = $request->cookies->get($cookieString);
        $hasBeenClosed = (bool) $cookie;

        return $this->render('@Dashboard/SiteNotice/showGlobalSiteNotice.html.twig', [
            'cookieString' => $cookieString,
            'date' => $this->noticeDate,
            'hasBeenClosed' => $hasBeenClosed,
        ]);
    }
}
