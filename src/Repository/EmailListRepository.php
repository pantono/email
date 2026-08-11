<?php

namespace Pantono\Email\Repository;

use Pantono\Database\Repository\DefaultRepository;

class EmailListRepository extends DefaultRepository
{
    public function getAllLists(): array
    {
        return $this->selectAll('email_list');
    }

    public function getEntryForEmail(\Pantono\Email\Model\EmailList $list, string $emailAddress): ?array
    {
        return $this->selectRowByValues('email_list', ['list_id' => $list->getId(), 'email_address' => $emailAddress]);
    }
}
