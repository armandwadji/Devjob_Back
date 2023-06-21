<?php

namespace App\EventSubcriber;

use App\Event\OfferDeleteEvent;
use App\Event\UserDeleteEvent;
use phpDocumentor\Reflection\Types\Void_;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class MailerEventSubcriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailerInterface
    ) {
    }
    public static function getSubscribedEvents()
    {
        return [
            OfferDeleteEvent::class => [
                ['onOfferDelete']
            ],
            UserDeleteEvent::class => [
                ['onUserDelete']
            ]
        ];
    }

    public function onOfferDelete(OfferDeleteEvent $offerDeleteEvent): void
    {

        // dd($offerDeleteEvent);
        $offer = $offerDeleteEvent->offer;

        $email = (new TemplatedEmail())
            ->from(new Address('admin@devjob.fr', 'Administrateur'));


        foreach ($offer->getCandidates() as $candidate) {
            $email->addBcc($candidate->getEmail());
        }


        $email->subject('Réponse candidature pour le poste :' . $offer->getName())
            ->htmlTemplate('emails/candidate_email.html.twig')
            ->context([
                'offer' => $offer,
                'company' => $offer->getCompany(),
                'contact' => $offer->getCompany()->getUser()
            ]);

        try {
            $this->mailerInterface->send($email);
        } catch (TransportExceptionInterface $transportException) {
            throw $transportException;
        }
    }

    public function onUserDelete(UserDeleteEvent $userDeleteEvent):Void
    {
        dd($userDeleteEvent);

                // // MAILER SEND USER
                // $this->mailerService->send(
                //     $user->getEmail(),
                //     'Demande de suppresion de compte.',
                //     'delete_account.html.twig',
                //     ['user' => $user]
                // );
        
                // // MAILER SEND ADMIN
                // $this->mailerService->send(
                //     'admin@devjob.com',
                //     'Demande de suppresion de compte.',
                //     'delete_account.html.twig',
                //     ['user' => $user]
                // );
        
    }
}
