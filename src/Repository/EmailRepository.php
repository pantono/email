<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\DefaultRepository;
use Pantono\Email\Model\EmailSend;
use Pantono\Email\Model\EmailMessage;

class EmailRepository extends DefaultRepository
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
        $id = $this->insertOrUpdate('email_send', 'id', $send->getId(), $send->getAllData());
        if ($id) {
            $send->setId($id);
        }
    }

    public function saveMessage(EmailMessage $message): void
    {
        $id = $this->insertOrUpdate('email_message', 'id', $message->getId(), $message->getAllData());
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

    public function getStatusById(int $id): ?array
    {
        return $this->selectSingleRow('email_status', 'id', $id);
    }

    public function getConfig(): ?array
    {
        $select = $this->getDb()->select('c.*')->from('email_config', 'c');
        return $this->getDb()->fetchRow($select);
    }
}
