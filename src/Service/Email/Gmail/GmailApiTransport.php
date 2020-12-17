<?php

namespace App\Service\Email\Gmail;

use Exception;
use Google_Client;
use Google_Exception;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Swift_DependencyContainer;
use Swift_DependencyException;
use Swift_Events_EventListener;
use Swift_Events_SendEvent;
use Swift_Events_SimpleEventDispatcher;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Swift_TransportException;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * Class GmailApiTransport.
 */
class GmailApiTransport implements Swift_Transport
{
    /**
     * @var FileLocator
     */
    private $fileLocator;

    /**
     * @var Swift_Events_SimpleEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $defaultAccount;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var Google_Service_Gmail[]
     */
    private $services = [];

    /**
     * GmailApiTransport constructor.
     *
     * @throws Swift_DependencyException
     */
    public function __construct(FileLocator $fileLocator, string $projectDirectory, string $defaultAccount)
    {
        $this->fileLocator      = $fileLocator;
        $this->eventDispatcher  = Swift_DependencyContainer::getInstance()->lookup('transport.eventdispatcher');
        $this->defaultAccount   = $defaultAccount;
        $this->projectDirectory = $projectDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return !empty($this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($evt = $this->eventDispatcher->createTransportChangeEvent($this)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeTransportStarted');
            if ($evt->bubbleCancelled()) {
                return;
            }
        }

        try {
            $this->getService($this->defaultAccount);
        } catch (Swift_TransportException $e) {
            $this->throwException($e);
        }

        if ($evt) {
            $this->eventDispatcher->dispatchEvent($evt, 'transportStarted');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if ($evt = $this->eventDispatcher->createTransportChangeEvent($this)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeTransportStopped');
            if ($evt->bubbleCancelled()) {
                return;
            }
        }

        if ($evt) {
            $this->eventDispatcher->dispatchEvent($evt, 'transportStopped');
        }

        $this->services = [];
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $keys = array_keys($message->getFrom());
        $from = reset($keys);
        if (!$from) {
            $message->setFrom($this->defaultAccount);
            $from = $this->defaultAccount;
        }

        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $msg = new Google_Service_Gmail_Message();
        $msg->setRaw($this->base64UrlEncode($message->toString()));

        $service = $this->getService($from);

        try {
            $service->users_messages->send('me', $msg);

            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }

            $message->generateId();

            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }

    /**
     * Get the Google_Service_Gmail associated with $account.
     *
     * @throws Swift_TransportException
     */
    private function getService(string $account): Google_Service_Gmail
    {
        if (!isset($this->services[$account])) {
            try {
                $this->services[$account] = new Google_Service_Gmail($this->getClient($account));
            } catch (Exception $e) {
                throw new Swift_TransportException($e->getMessage());
            }
        }

        return $this->services[$account];
    }

    /**
     * Get the Google_Client associated with $account.
     *
     * @throws Google_Exception
     */
    private function getClient(string $account): Google_Client
    {
        $client = new Google_Client();
        $client->setAuthConfig(
            $this->fileLocator->locate($this->projectDirectory.'/config/gmail/credentials/'.$account.'.json')
        );
        $client->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setConfig('subject', $account);

        return $client;
    }

    /**
     * Return $message base64urlencoded.
     */
    private function base64UrlEncode(string $message): string
    {
        return rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
    }

    /**
     * Throw a TransportException, first sending it to any listeners.
     *
     * @throws Swift_TransportException
     */
    private function throwException(Swift_TransportException $e)
    {
        if ($evt = $this->eventDispatcher->createTransportExceptionEvent($this, $e)) {
            $this->eventDispatcher->dispatchEvent($evt, 'exceptionThrown');
            if (!$evt->bubbleCancelled()) {
                throw $e;
            }
        } else {
            throw $e;
        }
    }
}
