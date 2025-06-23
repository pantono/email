<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\MysqlRepository;
use Pantono\Email\Model\EmailAddress;

class EmailAddressRepository extends MysqlRepository
{
    public function getEmailAddressByEmail(string $email): ?array
    {
        return $this->selectSingleRow('email_address', 'email', $email);
    }

    public function saveEmailAddress(EmailAddress $address): void
    {
        $data = [
            'email' => $address->getEmail(),
            'valid' => $address->isValid() ? 1 : 0,
            'last_checked' => $address->getLastChecked()->format('Y-m-d H:i:s'),
            'invalid_reason' => $address->getInvalidReason()
        ];
        $id = $this->insertOrUpdate('email_address', 'id', $address->getId(), $data);
        if ($id) {
            $address->setId($id);
        }
    }

    public function getConfig(): array
    {
        return $this->selectSingleRowFromQuery($this->getDb()->select()->from('email_config'));
    }

    public function getDisposableEmailDomain(string $domain): ?array
    {
        return $this->selectSingleRow('email_disposable_domain', 'domain', $domain);
    }

    public function saveDisposableDomain(string $domain): void
    {
        $this->insertIgnore('email_disposable_domain', ['domain' => $domain]);
    }
}
