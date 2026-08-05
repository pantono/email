<?php

namespace Pantono\Email\Task;

use Pantono\Queue\Task\AbstractTask;
use Symfony\Component\HttpFoundation\ParameterBag;
use Pantono\Email\Email;

class DoEmailSend extends AbstractTask
{
    private Email $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    public function process(ParameterBag $parameters): array
    {
        $sendId = $parameters->get('send_id');
        if (!$sendId) {
            return ['success' => false, 'error' => 'Send ID not present'];
        }
        $send = $this->email->getEmailSendById($parameters->get('send_id'));
        if (!$send) {
            return ['success' => false, 'error' => 'Send not found'];
        }

        $this->email->sendEmailSend($send);

        return ['success' => true, 'status' => $send->getStatus()->getName()];
    }
}
