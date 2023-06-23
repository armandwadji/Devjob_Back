<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Symfony\Component\Intl\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/my-account', name: 'user.')]
#[Security("is_granted('ROLE_USER') and user === choosenUser")]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        // private MailerService $mailerService
    ) {
    }

    /**
     * This controller display detail of company
     * @param User|null $choosenUser
     * @return Response
     */
    #[Route('/{id}', name: 'index', methods: ['GET'])]
    public function home(User $choosenUser): Response
    {
        return $this->render('pages/user/account.html.twig', [
            'user' => $choosenUser,
        ]);
    }

    /**
     * This controller edit companu profil
     * @param User|null $choosenUser
     * @param Request $request
     * @return Response
     */
    #[Route('/update/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(User $choosenUser = null, Request $request): Response
    {

        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        static::countryEncode($choosenUser);

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->getData()->getCompany()->getImageFile() && !(bool)stristr($form->getData()->getCompany()->getImageFile()->getmimeType(), "image")) {

                $this->addFlash(
                    type: 'warning',
                    message: 'Veuillez choisir une image.'
                );

                $form->getData()->getCompany()->setImageFile(null);
            } else {

                $choosenUser->getCompany()->setCountry(Countries::getAlpha3Name($choosenUser->getCompany()->getCountry())); //Convertis les initiales du pays en son nom complet.

                $this->userRepository->save($choosenUser, true);

                $choosenUser->getCompany()->setImageFile(null);

                $this->addFlash(
                    type: 'success',
                    message: 'Les informations de votre compte ont bien été modifiées.'
                );

                return $this->redirectToRoute('offer.index', [
                    'company' => $choosenUser->getCompany()->getId(),
                ]);
            }
        }

        return $this->render('pages/user/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This controller makes a request to delete a user account 
     * @param User $choosenUser
     * @param Request $request
     * @return Response
     */
    #[Route('/delete/{id}', name: 'delete', methods: ['GET', 'POST'])]
    public function deleteAccount(User $choosenUser, Request $request): Response
    {
        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        static::countryEncode($choosenUser);

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choosenUser->setIsDeleted(!$choosenUser->isIsDeleted());
            $choosenUser->setDescription($choosenUser->isIsDeleted() ? $choosenUser->getDescription() : null);
            $choosenUser->getCompany()->setCountry(Countries::getAlpha3Name($choosenUser->getCompany()->getCountry())); //Convertis les initiales du pays en son nom complet.

            if ($choosenUser->isIsDeleted()) {

                // static::sendEmail($choosenUser);
            }

            $this->userRepository->save($choosenUser, true);

            $this->addFlash(
                type: 'success',
                message: 'La demande de suppression de votre compte à été ' . ($choosenUser->isIsDeleted() ? 'éffectuer' : 'annuler') . ' avec succes.'
            );

            return $this->redirectToRoute('offer.index', [
                'company' => $choosenUser->getCompany()->getId(),
            ]);
        }

        return $this->render('pages/user/delete_account.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * This method convert country code in country name
     * @param \App\Entity\User $user
     * @return void
     */
    private function countryEncode(User $user)
    {
        $isoCode2 = array_search($user->getCompany()->getCountry(), Countries::getNames(), true);
        $isoCode3 = Countries::getAlpha3Code($isoCode2);
        $user->getCompany()->setCountry($isoCode3);
    }

    /**
     * This method that sends an email when requesting to delete an account
     * @param User $user
     * @return void
     */
    // private function sendEmail(User $user): void
    // {
    //     // MAILER SEND USER
    //     $this->mailerService->send(
    //         $user->getEmail(),
    //         'Demande de suppresion de compte.',
    //         'delete_account.html.twig',
    //         ['user' => $user]
    //     );

    //     // MAILER SEND ADMIN
    //     $this->mailerService->send(
    //         'admin@devjob.com',
    //         'Demande de suppresion de compte.',
    //         'delete_account.html.twig',
    //         ['user' => $user]
    //     );
    // }
}
