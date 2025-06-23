<?php

namespace Pantono\Email;

use Pantono\Email\Repository\EmailAddressRepository;
use Pantono\Hydrator\Hydrator;
use Pantono\Email\Model\EmailAddress;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Pantono\Email\Model\EmailConfig;
use Pantono\Email\Validator\DisposableEmailCheck;

class EmailAddresses
{
    public const VALID_RECHECK_INTERVAL = '30 day';
    public const INVALID_RECHECK_INTERVAL = '1 hour';
    public const DISPOSABLE_LIST = 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/refs/heads/main/disposable_email_blocklist.conf';
    private EmailAddressRepository $repository;
    private Hydrator $hydrator;
    private EmailValidator $emailValidator;

    public function __construct(EmailAddressRepository $repository, Hydrator $hydrator, EmailValidator $emailValidator)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->emailValidator = $emailValidator;
    }

    public function validateEmailAddress(string $address, ?\DateTimeImmutable $date = null): bool
    {
        $address = trim($address);
        if ($date === null) {
            $date = new \DateTimeImmutable();
        }
        $config = $this->getConfig();
        $email = $this->getEmailByAddress($address);
        if ($email === null) {
            $email = new EmailAddress();
            $email->setEmail($address);
            $email->setLastChecked($date->modify('-10 year'));
            $email->setValid(false);
        }
        $validators = [new RFCValidation()];
        if ($config->isCheckDns()) {
            $validators[] = new DNSCheckValidation();
        }
        if ($config->isCheckDisposableDomain()) {
            $validators[] = new DisposableEmailCheck($this->repository);
        }
        if ($config->isCheckSmtp()) {
            throw new \RuntimeException('Email smtp checking not yet implemented');
        }
        if ($email->needsRevalidating()) {
            $multipleValidations = new MultipleValidationWithAnd($validators);
            if (!$this->emailValidator->isValid($address, $multipleValidations)) {
                $error = $this->emailValidator->getError()->description();
                $email->setInvalidReason($error);
                $email->setValid(false);
                $email->setLastChecked($date);
                $this->saveEmailAddress($email);
                return false;
            }
            $email->setLastChecked($date);
            $email->setValid(true);
            $email->setInvalidReason(null);
            $this->saveEmailAddress($email);
        }
        return $email->isValid();
    }

    public function getEmailByAddress(string $address): ?EmailAddress
    {
        return $this->hydrator->hydrate(EmailAddress::class, $this->repository->getEmailAddressByEmail($address));
    }

    public function saveEmailAddress(EmailAddress $address): void
    {
        $this->repository->saveEmailAddress($address);
    }

    public function getConfig(): EmailConfig
    {
        return $this->hydrator->hydrate(EmailConfig::class, $this->repository->getConfig());
    }

    public function syncDisposableList(string $file): void
    {
        $contents = file_get_contents($file);
        foreach (explode(PHP_EOL, $contents) as $domain) {
            $domain = trim($domain);
            if ($domain) {
                $this->repository->saveDisposableDomain($domain);
            }
        }
    }
}
