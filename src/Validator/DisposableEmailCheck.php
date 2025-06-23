<?php

namespace Pantono\Email\Validator;

use Egulias\EmailValidator\Validation\EmailValidation;
use Pantono\Email\Repository\EmailAddressRepository;
use Egulias\EmailValidator\Warning\Warning;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\EmailLexer;
use Pantono\Email\Validator\Reason\DisposableEmailNotAllowed;

class DisposableEmailCheck implements EmailValidation
{
    private EmailAddressRepository $repository;
    private ?InvalidEmail $error = null;

    public function __construct(EmailAddressRepository $repository)
    {
        $this->repository = $repository;
    }

    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        [, $domain] = explode('@', $email, 2);
        $domain = $this->repository->getDisposableEmailDomain($domain);
        if ($domain !== null) {
            $this->error = new InvalidEmail(new DisposableEmailNotAllowed(), '');
            return false;
        }
        return true;
    }

    public function getError(): ?InvalidEmail
    {
        return $this->error;
    }

    public function getWarnings(): array
    {
        return [];
    }
}
