<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\DefaultRepository;
use Pantono\Email\Model\EmailList;

class EmailListRepository extends DefaultRepository
{
    public function getAllLists(): array
    {
        return $this->selectAll('email_list');
    }

    public function getEntryForEmail(EmailList $list, string $emailAddress): ?array
    {
        return $this->selectRowByValues('email_list_entry', ['list_id' => $list->getId(), 'email_address' => $emailAddress]);
    }
}
