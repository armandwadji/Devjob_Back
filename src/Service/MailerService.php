<?php

namespace App\Service;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Service permettant de générer un email
 */
class MailerService
{

    public function __construct(private readonly MailerInterface $mailerInterface)
    {
    }

    /**
     * This methode send Email
     * @param string $to
     * @param string $subject
     * @param string $templateTwig
     * @param array $context
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send(string $to, string $subject, string $templateTwig, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('admin@devjobs.wadji.cefim.o2switch.site', 'Devjob'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('emails/' . $templateTwig)
            ->context($context);

        $this->mailerInterface->send($email);
    }
}
