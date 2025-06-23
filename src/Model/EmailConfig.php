<?php

namespace Pantono\Email\Model;

class EmailConfig
{
    private bool $checkDns;
    private bool $checkSmtp;
    private bool $checkDisposableDomain;

    public function isCheckDns(): bool
    {
        return $this->checkDns;
    }

    public function setCheckDns(bool $checkDns): void
    {
        $this->checkDns = $checkDns;
    }

    public function isCheckSmtp(): bool
    {
        return $this->checkSmtp;
    }

    public function setCheckSmtp(bool $checkSmtp): void
    {
        $this->checkSmtp = $checkSmtp;
    }

    public function isCheckDisposableDomain(): bool
    {
        return $this->checkDisposableDomain;
    }

    public function setCheckDisposableDomain(bool $checkDisposableDomain): void
    {
        $this->checkDisposableDomain = $checkDisposableDomain;
    }
}
