<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Event\UserDeleteEvent;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/my-account', name: 'user.', requirements: ['id' => '\d+'])]
#[Security("is_granted('ROLE_USER') and user === choosenUser")]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher
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
        return $this->render('pages/user/account.html.twig', ['user' => $choosenUser]);
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
        $choosenUser->countryEncode();

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageIsInvalid = $choosenUser->getCompany()->getImageFile() && !(bool) stristr($choosenUser->getCompany()->getImageFile()->getmimeType(), "image");

            if (!$imageIsInvalid) {

                $choosenUser->countryDecode(); //Convertis les initiales du pays en son nom complet.
                $this->userRepository->save($choosenUser, true);
                $choosenUser->getCompany()->setImageFile(null);
                $this->addFlash(type: 'success', message: 'Les informations de votre compte ont bien été modifiées.');

                return $this->redirectToRoute('offer.index', ['company' => $choosenUser->getCompany()->getId()]);
            } 

            $this->addFlash(type: 'warning', message: 'Veuillez choisir une image.');
            $form->getData()->getCompany()->setImageFile(null);
        }

        return $this->render('pages/user/update.html.twig', ['form' => $form->createView()]);
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
        $choosenUser->countryEncode();

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choosenUser->setIsDeleted(!$choosenUser->isIsDeleted());
            $choosenUser->setDescription($choosenUser->isIsDeleted() ? $choosenUser->getDescription() : null);
            $choosenUser->countryDecode(); //Convertis les initiales du pays en son nom complet.

            if ($choosenUser->isIsDeleted()) $this->eventDispatcher->dispatch(new UserDeleteEvent($choosenUser));

            $this->userRepository->save($choosenUser, true);

            $this->addFlash(
                type: 'success',
                message: 'La demande de suppression de votre compte à été ' . ($choosenUser->isIsDeleted() ? 'éffectuer' : 'annuler') . ' avec succes.'
            );

            return $this->redirectToRoute('offer.index', ['company' => $choosenUser->getCompany()->getId()]);
        }

        return $this->render('pages/user/delete_account.html.twig', ['form' => $form]);
    }
}
