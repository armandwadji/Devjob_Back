<?php

namespace App\EventSubscriber;

use App\Event\UserDeleteEvent;
use App\Service\MailerService;
use App\Event\OfferDeleteEvent;
use App\Event\CandidateDeleteEvent;
use Symfony\Component\Mime\Address;
use App\Event\UserForgetPasswordEvent;
use App\Event\UserTokenRegistrationEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;


class MailerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailerInterface,
        private MailerService $mailerService,
        private TokenGeneratorInterface $tokenGeneratorInterface
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            OfferDeleteEvent::class => [
                ['onOfferDelete']
            ],
            CandidateDeleteEvent::class => [
                ['onCandidateDelete']
            ],
            UserDeleteEvent::class => [
                ['onUserDelete']
            ],
            UserTokenRegistrationEvent::class => [
                ['onTokenRegistration']
            ],
            UserForgetPasswordEvent::class => [
                ['onUserForgetPassword']
            ]
        ];
    }

    /**
     * This controller sends an email to all candidates who have applied for the offer delete
     * @param \App\Event\OfferDeleteEvent $offerDeleteEvent
     * @return void
     */
    public function onOfferDelete(OfferDeleteEvent $offerDeleteEvent): void
    {
        $offer = $offerDeleteEvent->offer;

        $email = (new TemplatedEmail())->from(new Address('admin@devjobs.wadji.cefim.o2switch.site', 'Devjob'));

        foreach ($offer->getCandidates() as $candidate) {
            $email->addBcc($candidate->getEmail());
        }

        $email->subject('Réponse candidature pour le poste :' . $offer->getName())
            ->htmlTemplate('emails/candidates_email.html.twig')
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

    /**
     * This controller sends an email to a candidate when his application is deleted
     * @param \App\Event\CandidateDeleteEvent $candidateDeleteEvent
     * @return void
     */
    public function onCandidateDelete(CandidateDeleteEvent $candidateDeleteEvent): void
    {
        $candidate = $candidateDeleteEvent->candidate;

        $this->mailerService->send(
            to: $candidate->getEmail(),
            subject: 'Réponse candidature pour le poste :' . $candidate->getOffer()->getName(),
            templateTwig: 'candidate_email.html.twig',
            context: ['candidate' => $candidateDeleteEvent->candidate]
        );
    }

    /**
     * This controller sends an email to the company and to the administrator when requesting the deletion of the account of the company which requests it
     * @param \App\Event\UserDeleteEvent $userDeleteEvent
     * @return void
     */
    public function onUserDelete(UserDeleteEvent $userDeleteEvent): Void
    {
        $user = $userDeleteEvent->user;

        // MAILER SEND USER
        $this->mailerService->send(
            to: $user->getEmail(),
            subject: 'Demande de suppresion de compte.',
            templateTwig: 'delete_account.html.twig',
            context: ['user' => $user]
        );

        // MAILER SEND ADMIN
        $this->mailerService->send(
            to: 'admin@devjobs.wadji.cefim.o2switch.site',
            subject: 'Demande de suppresion de compte.',
            templateTwig: 'delete_account.html.twig',
            context: ['user' => $user]
        );
    }

    /**
     * This controller sends an email to a user for the confirmation of his account
     * @param \App\Event\UserTokenRegistrationEvent $userTokenRegistration
     * @return void
     */
    public function onTokenRegistration(UserTokenRegistrationEvent $userTokenRegistration): void
    {
        $user = $userTokenRegistration->user;

        $this->mailerService->send(
            $user->getEmail(),
            'Confirmation du compte utilisateur',
            'registration_confirmation.html.twig',
            [
                'user'          => $user,
                'token'         => $user->getTokenRegistration(),
                'lifeTimetoken' => $user->getTokenRegistrationLifeTime()->format('d/m/Y à H:i:s')
            ]
        );
    }

    /**
     * This controller sends an email to a user to reset his password
     * @param \App\Event\UserForgetPasswordEvent $userForgetPasswordEvent
     * @return void
     */
    public function onUserForgetPassword(UserForgetPasswordEvent $userForgetPasswordEvent): void
    {

        $user = $userForgetPasswordEvent->user;

        $this->mailerService->send(
            $user->getEmail(),
            'Modiffication mots de passe utilisateur',
            'change_password_email.html.twig',
            [
                'user'  => $user,
                'token' => $user->getTokenRegistration(),
            ]
        );
    }
}
