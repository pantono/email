<?php

namespace Pantono\Email;

use Symfony\Component\Mailer\Mailer;
use Pantono\Email\Repository\EmailRepository;
use Pantono\Hydrator\Hydrator;
use Pantono\Email\Model\EmailSend;
use Pantono\Email\Model\EmailMessage;
use Pantono\Email\Model\MessageGenerator;
use Pantono\Email\Exception\SubjectIsRequiredException;
use Pantono\Email\Exception\ToAddressRequired;
use Pantono\Email\Exception\InvalidToAddress;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Pantono\Email\Event\PreEmailSendEvent;
use Pantono\Email\Event\PostEmailSendEvent;
use Pantono\Email\Model\EmailStatus;
use Pantono\Email\Model\EmailTemplate;

class Email
{
    private Mailer $mailer;
    private EmailRepository $repository;
    private Hydrator $hydrator;
    private EmailAddresses $emailAddresses;
    private EventDispatcher $dispatcher;
    private EmailTemplates $templates;
    public const STATUS_PENDING = 1;
    public const STATUS_SENT = 2;
    public const STATUS_DELIVERED = 3;
    public const STATUS_SOFT_BOUNCE = 4;
    public const STATUS_HARD_BOUNCE = 5;
    public const STATUS_COMPLAINED = 6;
    public const STATUS_ERROR = 7;

    public function __construct(
        Mailer          $mailer,
        EmailRepository $repository,
        Hydrator        $hydrator,
        EmailAddresses  $emailAddresses,
        EventDispatcher $dispatcher,
        EmailTemplates  $templates,
    )
    {
        $this->mailer = $mailer;
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->emailAddresses = $emailAddresses;
        $this->dispatcher = $dispatcher;
        $this->templates = $templates;
    }

    public function getEmailSendById(int $id): ?EmailSend
    {
        return $this->hydrator->hydrate(EmailSend::class, $this->repository->getEmailSendById($id));
    }

    public function getEmailMessageById(int $id): ?EmailMessage
    {
        return $this->hydrator->hydrate(EmailMessage::class, $this->repository->getEmailMessageById($id));
    }

    /**
     * @return EmailSend[]
     */
    public function getSendsForEmail(EmailMessage $message): array
    {
        return $this->hydrator->hydrateSet(EmailSend::class, $this->repository->getSendsForEmail($message));
    }

    public function createMessageForType(string $type): ?MessageGenerator
    {
        $template = $this->templates->getTemplateForType($type);
        if (!$template) {
            return null;
        }
        return $this->createMessageFromTemplate($template);
    }

    public function createMessageFromTemplate(EmailTemplate $template): MessageGenerator
    {
        $html = $this->templates->renderTemplate($template);
        $text = strip_tags($html);
        return $this->createMessage()->setRenderedHtml($html)->setRenderedText($text)->setTemplate($template);
    }

    public function sendInkyTemplate(string $toAddress, string $toName, string $inkyTemplate, array $variables = [], ?string $fromAddress = null, ?string $fromName = null): EmailMessage
    {
        $variables['content'] = $inkyTemplate;
        $email = $this->createMessage()
            ->subject('test')
            ->template('email/inky-template.twig')
            ->setVariables($variables)
            ->to($toAddress, $toName);

        if ($fromAddress) {
            $email->from($fromAddress, $fromName ?: $fromAddress);
        }

        $this->sendEmail($email);

        return $email->getMessage();
    }

    public function createMessage(): MessageGenerator
    {
        $config = $this->getEmailConfig();
        return (new MessageGenerator($this))->from($config['default_from_address'], $config['default_from_name']);
    }

    public function sendEmail(MessageGenerator $email): void
    {
        $this->validateMessageGenerator($email);
        $this->renderEmail($email);
        $message = $email->generateMessageModel();
        $this->repository->saveMessage($message);
        $pendingStatus = $this->getStatusById(self::STATUS_PENDING);
        if (!$pendingStatus) {
            throw new \RuntimeException('Pending status does not exist');
        }
        $sentStatus = $this->getStatusById(self::STATUS_SENT);
        if (!$sentStatus) {
            throw new \RuntimeException('Sent status does not exist');
        }
        $errorStatus = $this->getStatusById(self::STATUS_ERROR);
        if (!$errorStatus) {
            throw new \RuntimeException('Error status does not exist');
        }
        foreach ($email->getToAddresses() as $address) {
            $send = $message->createEmailSend($address->getAddress(), $address->getName());
            $send->setDateSent(new \DateTimeImmutable());
            $send->setStatus($pendingStatus);
            $mailerSend = $send->createSymfonyModel();
            $send->setMessageId($mailerSend->generateMessageId());
            $this->repository->saveEmailSend($send);
            $mailerSend->html($this->replaceTracking($message->getHtmlMessage(), $send->getTrackingKey()));
            try {
                $mailerSend->ensureValidity();
                $preSendEvent = new PreEmailSendEvent();
                $preSendEvent->setSend($send);
                $this->dispatcher->dispatch($preSendEvent);
                $this->mailer->send($mailerSend);
                $send->setStatus($sentStatus);
            } catch (\Exception $e) {
                $send->setStatus($errorStatus);
                $send->setErrorMessage($e->getMessage());
            }
            $this->repository->saveEmailSend($send);
            $postSendEvent = new PostEmailSendEvent();
            $postSendEvent->setSend($send);
            $this->dispatcher->dispatch($postSendEvent);
        }
    }

    private function validateMessageGenerator(MessageGenerator $email): void
    {
        if ($email->getSubject() === null) {
            throw new SubjectIsRequiredException('Subject is required to send an e-mail');
        }
        if (empty($email->getToAddresses())) {
            throw new ToAddressRequired('At least 1 to address is required to send an e-mail');
        }
        foreach ($email->getToAddresses() as $address) {
            $valid = $this->emailAddresses->validateEmailAddress($address->getAddress());
            if ($valid === false) {
                $email->removeToAddress($address->getAddress());
            }
        }
        if (empty($email->getToAddresses())) {
            throw new InvalidToAddress('No valid e-mail addresses to send to');
        }
    }

    public function renderEmail(MessageGenerator $email): void
    {
        $htmlContent = $this->templates->renderTemplate($email->getTemplate(), $email->getContext());
        $email->setRenderedHtml($htmlContent);
        $textContent = $this->generatePlainText($htmlContent);
        $email->setRenderedText($textContent);
    }

    public function generatePlainText(string $html): string
    {
        $html = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
        $html = preg_replace("/<style\\b[^>]*>(.*?)<\\/style>/s", "", $html);
        $plainText = strip_tags($html);
        $plainText = trim($plainText);

        $lines = explode("\n", $plainText);
        $lines = array_filter($lines, function (string $line): bool {
            return trim($line) ? true : false;
        });
        array_walk($lines, function (&$line) {
            $line = trim($line);
        });
        $plainText = implode("\n", $lines);

        return trim($plainText);
    }

    private function replaceTracking(string $content, string $code): string
    {
        return str_replace('__TRACKING_CODE__', $code, $content);
    }

    public function addLogToSend(EmailSend $send, string $entry): void
    {
        $this->repository->addLogToSend($send, $entry);
    }

    public function getStatusById(int $id): ?EmailStatus
    {
        return $this->hydrator->hydrate(EmailStatus::class, $this->repository->getStatusById($id));
    }

    private function getEmailConfig(): array
    {
        $config = $this->repository->getConfig();
        if ($config === null) {
            $config = [
                'check_dns' => '1',
                'check_smtp' => '0',
                'check_disposable_domain' => 1,
                'default_from_name' => 'Pantono',
                'default_from_address' => 'noreply@pantono.com',
            ];
        }
        return $config;
    }
}
