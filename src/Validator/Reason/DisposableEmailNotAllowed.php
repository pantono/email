<?php

namespace Pantono\Email\Validator\Reason;

use Egulias\EmailValidator\Result\Reason\Reason;

class DisposableEmailNotAllowed implements Reason
{
    public function code(): int
    {
        return 1001;
    }

    public function description(): string
    {
        return 'Disposable e-mail addresses are not allowed';
    }
}
