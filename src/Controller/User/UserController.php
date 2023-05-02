<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserType;
use App\Service\MailerService;
use Symfony\Component\Intl\Countries;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * This controller display detail of company
     * @param User|null $choosenUser
     * @return Response
     */
    #[Route('/my-account/{id}', name: 'user.index', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
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
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/my-account/update/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    public function edit(User $choosenUser = null, Request $request, EntityManagerInterface $manager): Response
    {

        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        static::countryEncode($choosenUser);

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->getData()->getCompany()->getImageFile() && !(bool)stristr($form->getData()->getCompany()->getImageFile()->getmimeType(), "image")) {

                $this->addFlash(
                    type    : 'warning',
                    message : 'Veuillez choisir une image.'
                );

                $form->getData()->getCompany()->setImageFile(null);
            } else {

                $user = $form->getData();
                $user->getCompany()->setCountry(Countries::getAlpha3Name($user->getCompany()->getCountry())); //Convertis les initiales du pays en son nom complet.

                $manager->persist($user);
                $manager->flush();
                $user->getCompany()->setImageFile(null);

                $this->addFlash(
                    type    : 'success',
                    message : 'Les informations de votre compte ont bien été modifiées.'
                );

                return $this->redirectToRoute('offer.index', [
                    'company' => $user->getCompany()->getId(),
                ]);
            }
        }

        return $this->render('pages/user/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/my-account/delete/{id}', name: 'user.delete', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    public function deleteAccount(User $choosenUser, Request $request, EntityManagerInterface $manager, MailerService $mailerService): Response
    {
        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        static::countryEncode($choosenUser);

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choosenUser->setIsDeleted(!$choosenUser->isIsDeleted());
            $choosenUser->setDescription($choosenUser->isIsDeleted() ? $choosenUser->getDescription() : null);
            $choosenUser->getCompany()->setCountry(Countries::getAlpha3Name($choosenUser->getCompany()->getCountry())); //Convertis les initiales du pays en son nom complet.

            if($choosenUser->isIsDeleted()){

                // MAILER SEND USER
                $mailerService->send(
                    $choosenUser->getEmail(),
                    'Demande de suppresion de compte.',
                    'delete_account.html.twig',
                    ['user' => $choosenUser]
                );
    
                // MAILER SEND ADMIN
                $mailerService->send(
                    'admin@devjob.com',
                    'Demande de suppresion de compte.',
                    'delete_account.html.twig',
                    ['user' => $choosenUser]
                );
            }

            $manager->persist($choosenUser);
            $manager->flush();

            $this->addFlash(
                type    : 'success',
                message : 'La demande de suppression de votre compte à été ' . ($choosenUser->isIsDeleted() ? 'éffectuer' : 'annuler') . ' avec succes.'
            );

            return $this->redirectToRoute('offer.index', [
                'company' => $choosenUser->getCompany()->getId(),
            ]);
        }

        return $this->render('pages/user/delete_account.html.twig', [
            'form' => $form,
        ]);
    }

    private function countryEncode(User $user)
    {
        $isoCode2 = array_search($user->getCompany()->getCountry(), Countries::getNames(), true);
        $isoCode3 = Countries::getAlpha3Code($isoCode2);
        $user->getCompany()->setCountry($isoCode3);
    }
}
