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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Mailer\Message;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * The mail message factory builds mail messages. These messages are set with a translatable title and their message
 * is based on a twig template.
 *
 * The sender and receiver can be configured in the parameters.yml
 */
class MailMessageFactory
{
    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $receiver;

    /**
     * @var string
     */
    private $noReply;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $noReply
     * @param TranslatorInterface $translator
     * @param EngineInterface $templating
     */
    public function __construct(
        $sender,
        $receiver,
        $noReply,
        TranslatorInterface $translator,
        EngineInterface $templating
    ) {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->noReply = $noReply;
        $this->translator = $translator;
        $this->templating = $templating;
    }

    public function buildPublishToProductionMessage(Entity $entity)
    {
        $message = $this->createNewMessage();
        $message->setSubject(
            $this->translator->trans(
                'mail.confirmation.publish_production.subject',
                ['%entityId%' => $entity->getEntityId()]
            )
        );

        $template = $this->renderView(
            '@Dashboard/Mail/published.html.twig',
            ['entity' => $entity]
        );

        $message->setBody($template, 'text/html');

        return $message;
    }

    private function createNewMessage()
    {
        $message = Message::newInstance();

        $headers = $message->getHeaders();
        $headers->addTextHeader('Auto-Submitted', 'auto-generated');
        # TODO: see https://github.com/swiftmailer/swiftmailer/issues/705
        $message->setReturnPath($this->noReply);
        $message->setFrom($this->sender);
        $message->setTo($this->receiver);

        return $message;
    }

    private function renderView($view, array $parameters = array())
    {
        return $this->templating->render($view, $parameters);
    }
}
