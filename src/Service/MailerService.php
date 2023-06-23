<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Service permettant de générer un email
 */
class MailerService
{

    // public function __construct(private MailerInterface $mailerInterface)
    // {
    // }

    // /**
    //  * This methode send Email
    //  * @param string $to
    //  * @param string $subject
    //  * @param string $templateTwig
    //  * @param array $context
    //  * @return void
    //  */
    // public function send(string $to, string $subject, string $templateTwig, array $context): void
    // {
    //     $email = (new TemplatedEmail())
    //         ->from(new Address('admin@devjob.fr', 'Administrateur'))
    //         ->to($to)
    //         ->subject($subject)
    //         ->htmlTemplate('emails/' . $templateTwig)
    //         ->context($context);

    //     try {
    //         $this->mailerInterface->send($email);
    //     } catch (TransportExceptionInterface $transportException) {
    //         throw $transportException;
    //     }
    // }
}
