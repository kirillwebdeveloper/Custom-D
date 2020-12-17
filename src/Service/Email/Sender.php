<?php

namespace App\Service\Email;

use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;

/**
 * Class Sender.
 */
class Sender
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $contactEmail;

    /**
     * Sender constructor.
     */
    public function __construct(Swift_Mailer $mailer, string $contactEmail)
    {
        $this->mailer       = $mailer;
        $this->contactEmail = $contactEmail;
    }

    /**
     * Send email contact.
     *
     * @param mixed $to
     */
    public function sendEmailContact($to, string $subject, string $body, ?string $inReplyTo = null, array $attachments = []): int
    {
        return $this->sendEmail(
            $this->contactEmail,
            $to,
            $subject,
            $body,
            $inReplyTo,
            $attachments
        );
    }

    /**
     * Send email.
     *
     * @param mixed $to
     */
    public function sendEmail(string $from, $to, string $subject, string $body, ?string $inReplyTo = null, array $attachments = []): int
    {
        $message = (new Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body, 'text/html');

        if ($inReplyTo) {
            $message->getHeaders()->addParameterizedHeader('In-Reply-To', '<'.$inReplyTo.'>');
            $message->getHeaders()->addParameterizedHeader('References', '<'.$inReplyTo.'>');
        }

        foreach ($attachments as $attachment) {
            if ($attachment instanceof Swift_Attachment) {
                $file = $attachment;
            } elseif (@is_file($attachment)) {
                $file = Swift_Attachment::fromPath($attachment);
            } else {
                $file = new Swift_Attachment($attachment);
            }
            $message->attach($file);
        }

        return $this->mailer->send($message);
    }
}
