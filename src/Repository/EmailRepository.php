<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\MysqlRepository;
use Pantono\Email\Model\EmailSend;
use Pantono\Email\Model\EmailMessage;

class EmailRepository extends MysqlRepository
{
    public function getEmailMessageById(int $id): ?array
    {
        return $this->selectSingleRow('email_message', 'id', $id);
    }

    public function getEmailSendById(int $id): ?array
    {
        return $this->selectSingleRow('email_send', 'id', $id);
    }

    public function saveEmailSend(EmailSend $send): void
    {
        $data = [
            'email_message_id' => $send->getMessage()->getId(),
            'message_id' => $send->getMessageId(),
            'to_address' => $send->getToAddress(),
            'to_name' => $send->getToName(),
            'date_sent' => $send->getDateSent()?->format('Y-m-d H:i:s'),
            'status' => $send->getStatus(),
            'error_message' => $send->getErrorMessage(),
            'tracking_key' => $send->getTrackingKey()
        ];
        $id = $this->insertOrUpdate('email_send', 'id', $send->getId(), $send->getAllData());
        if ($id) {
            $send->setId($id);
        }
    }

    public function saveMessage(EmailMessage $message): void
    {
        $data = [
            'from_address' => $message->getFromAddress(),
            'from_name' => $message->getFromName(),
            'subject' => $message->getSubject(),
            'date_added' => $message->getDateAdded()->format('Y-m-d H:i:s'),
            'text_message' => $message->getTextMessage(),
            'html_message' => $message->getHtmlMessage()
        ];
        $id = $this->insertOrUpdate('email_message', 'id', $message->getId(), $data);
        if ($id) {
            $message->setId($id);
        }
    }

    public function getSendsForEmail(EmailMessage $message): array
    {
        return $this->selectRowsByValues('email_send', ['email_message_id' => $message->getId()]);
    }

    public function addLogToSend(EmailSend $send, string $entry): void
    {
        $this->getDb()->insert('email_send_log', [
            'send_id' => $send->getId(),
            'date' => (new \DateTime)->format('Y-m-d H:i:s'),
            'entry' => $entry
        ]);
    }
}
