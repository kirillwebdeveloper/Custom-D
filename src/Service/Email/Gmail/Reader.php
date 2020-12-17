<?php

namespace App\Service\Email\Gmail;

use App\Modele\Email\Email;
use DateTime;
use Exception;
use Generator;
use Google_Client;
use Google_Exception;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use stdClass;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * Class Reader.
 */
class Reader
{
    /** @var FileLocator */
    private $fileLocator;

    /** @var string */
    private $projectDirectory;

    /** @var string */
    protected $email;

    /** @var Google_Client */
    protected $client;

    /** @var Google_Service_Gmail */
    protected $service;

    /**
     * Reader constructor.
     */
    public function __construct(FileLocator $fileLocator, string $projectDirectory)
    {
        $this->fileLocator      = $fileLocator;
        $this->projectDirectory = $projectDirectory;
    }

    /**
     * Get email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get auth file.
     *
     * @throws Exception
     */
    private function getAuthFile(): string
    {
        if (!$this->getEmail()) {
            throw new Exception('Email missing');
        }

        return $this->fileLocator->locate(
            $this->projectDirectory.'/config/gmail/credentials/'.$this->getEmail().'.json'
        );
    }

    /**
     * Get client.
     *
     * @throws Google_Exception
     */
    private function getClient(): Google_Client
    {
        if (null === $this->client) {
            $googleAuthJson = json_decode(
                file_get_contents($this->getAuthFile()),
                true
            );

            $client = new Google_Client();
            $client->setAuthConfig($googleAuthJson);
            $client->addScope(
                [
                    Google_Service_Gmail::MAIL_GOOGLE_COM,
                    Google_Service_Gmail::GMAIL_READONLY,
                    Google_Service_Gmail::GMAIL_MODIFY,
                ]
            );
            $client->setConfig('subject', $this->getEmail());
            $client->setAccessType('offline');

            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * Get service.
     *
     * @return Google_Service_Gmail
     */
    private function getService()
    {
        if (null === $this->service) {
            $this->service = new Google_Service_Gmail($this->getClient());
        }

        return $this->service;
    }

    /**
     * Process headers.
     */
    private function processHeaders(Email $email, Google_Service_Gmail_Message $message): void
    {
        $headers = $message->getPayload()->getHeaders();
        foreach ($headers as $header) {
            if ('Subject' === $header->getName()) {
                $email->setSubject($header->getValue());
            } elseif ('Date' === $header->getName()) {
                $email->setDate(new DateTime($header->getValue()));
            } elseif ('From' === $header->getName()) {
                $message_sender = str_replace('"', '', $header->getValue());
                if (preg_match('#(.*)<(.*)>#', $message_sender, $matches)) {
                    $email->setFrom(trim($matches[2]));
                    $email->setFromName(trim($matches[1]));
                } else {
                    $email->setFrom($message_sender);
                }
            } elseif ('In-Reply-To' === $header->getName()) {
                $email->setInReplyTo(str_replace(['<', '>'], '', $header->getValue()));
            } elseif ('Message-ID' === $header->getName()) {
                $email->setMessageId(str_replace(['<', '>'], '', $header->getValue()));
            }
        }
    }

    /**
     * Process parts.
     *
     * @param Google_Service_Gmail_MessagePart|Google_Service_Gmail_MessagePart[] $parts
     */
    private function processParts(Email $email, $parts)
    {
        if (!is_array($parts)) {
            return;
        }

        $service = $this->getService();

        foreach ($parts as $part) {
            $nameFile = $part->getFilename();
            if (!empty($nameFile)) {
                if ($part->getBody()->getAttachmentId()) {
                    $attachment = $service->users_messages_attachments->get(
                        'me',
                        $email->getId(),
                        $part->getBody()->getAttachmentId()
                    );
                    $email->addAttachment($attachment, $part->getMimeType(), $nameFile);
                }
            } else {
                $email->addData($part->getBody()->getData(), $part->getMimeType());
            }

            $this->processParts($email, $part->getParts());
        }
    }

    /**
     * Decode body.
     *
     * @return bool|string
     */
    public function decodeBody(string $body)
    {
        $sanitizedData = strtr($body, '-_', '+/');

        return base64_decode($sanitizedData);
    }

    /**
     * Get Messages.
     *
     * @return Generator|Email[]
     */
    public function getMessages(string $term = null)
    {
        $service = $this->getService();

        foreach ($this->getRawMessages($term) as $rawMessage) {
            $message = $service->users_messages->get('me', $rawMessage->id, ['format' => 'full']);

            $email = new Email();
            $email->setId($rawMessage->id);

            $this->processHeaders($email, $message);
            $this->processParts($email, $message->getPayload()->getParts());

            $email->addData($message->getPayload()->getBody()->getData(), $message->getPayload()->getMimeType());

            if ($email->hasData('text/html')) {
                $email->setContent($this->decodeBody($email->getData('text/html')));
            } elseif ($email->hasData('text/plain')) {
                $email->setContent($this->decodeBody($email->getData('text/plain')));
            }

            yield $email;
        }
    }

    /**
     * Trash message.
     */
    public function trashMessage(string $emailId): void
    {
        $this->getService()->users_messages->trash('me', $emailId);
    }

    /**
     * Get raw messages.
     *
     * @return stdClass[]
     */
    private function getRawMessages(string $term = null)
    {
        $service = $this->getService();

        $searchDefaultsOpts = [
            'includeSpamTrash' => true,
        ];
        if ($term) {
            $searchDefaultsOpts['q'] = $term;
        }
        $messages  = [];
        $pageToken = null;
        do {
            try {
                $searchOpts = $searchDefaultsOpts;
                if ($pageToken) {
                    $searchOpts['pageToken'] = $pageToken;
                }
                $messagesResponse = $service->users_messages->listUsersMessages('me', $searchOpts);
                if ($messagesResponse->getMessages()) {
                    $messages  = array_merge($messages, $messagesResponse->getMessages());
                    $pageToken = $messagesResponse->getNextPageToken();
                } else {
                    $pageToken = false;
                }
            } catch (Exception $e) {
            }
        } while ($pageToken);

        return $messages;
    }
}
