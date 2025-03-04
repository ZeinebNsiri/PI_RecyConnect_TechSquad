<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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

    public function sendEmail($to, $subject, $article_name,$article_cat ): void
    {
        
        $email = (new TemplatedEmail())
            ->from('recyconnect.techsquad@gmail.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('categorie_article/email.html.twig')
            ->context([
                'article' => $article_name,
                'categorie' => $article_cat,
            ])
            ->embedFromPath(__DIR__.'/../../public/frontOffice/img/mainlogo.png', 'logo_cid');
           

        $this->mailer->send($email);
    }
}



