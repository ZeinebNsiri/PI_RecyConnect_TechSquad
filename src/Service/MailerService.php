<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer) 
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($to, $subject, $content): void
    {
        $email = (new Email())
            ->from('recyconnect.techsquad@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);
    }
}



