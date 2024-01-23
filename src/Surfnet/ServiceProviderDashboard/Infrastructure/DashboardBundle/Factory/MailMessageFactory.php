<?php

//declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory;

use Exception;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The mail message factory builds mail messages. These messages are set with a translatable title and their message
 * is based on a twig template.
 *
 * The sender and receiver can be configured in the .env
 */
class MailMessageFactory
{
    public function __construct(
        private readonly string $sender,
        private readonly string $receiver,
        private readonly string $noReply,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildJiraIssueFailedMessage(Exception $exception, ManageEntity $entity): TemplatedEmail
    {
        $message = $this->createNewMessage();
        $message
            ->subject($this->translator->trans('mail.jira.publish_production_failed.subject'))
            ->htmlTemplate('@Dashboard/Mail/jiraPublicationFailed.html.twig')
            ->context(
                [
                'exception' => $exception,
                'entityId' => $entity->getMetaData()->getEntityId(),
                'serviceName' => $entity->getService()->getName(),
                ]
            );

        return $message;
    }

    private function createNewMessage(): TemplatedEmail
    {
        $email = new TemplatedEmail();
        $headers = $email->getHeaders();
        $headers->addTextHeader('Auto-Submitted', 'auto-generated');
        $email->returnPath($this->noReply);
        $email->from($this->sender);
        $email->to($this->receiver);

        return $email;
    }
}
