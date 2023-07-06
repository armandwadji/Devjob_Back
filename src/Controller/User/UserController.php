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
#[Security("is_granted('ROLE_USER') and user === chosenUser")]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository  $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * This controller display detail of company
     * @param User $chosenUser
     * @return Response
     */
    #[Route('/{id}', name: 'index', methods: ['GET'])]
    public function home(User $chosenUser): Response
    {
        return $this->render('pages/user/account.html.twig', ['user' => $chosenUser]);
    }

    /**
     * This controller edit companu profil
     * @param User|null $chosenUser
     * @param Request $request
     * @return Response
     */
    #[Route('/update/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(User $chosenUser, Request $request): Response
    {
        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        $chosenUser->countryEncode();

        $form = $this->createForm(UserType::class, $chosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageIsInvalid = $chosenUser->getCompany()->getImageFile() && !(bool) stristr($chosenUser->getCompany()->getImageFile()->getmimeType(), "image");

            if (!$imageIsInvalid) {

                $chosenUser->countryDecode(); //Convertis les initiales du pays en son nom complet.

                $this->userRepository->save($chosenUser, true);

                $chosenUser->getCompany()->setImageFile(null);

                $this->addFlash(type: 'success', message: 'Les informations de votre compte ont bien été modifiées.');

                return $this->redirectToRoute('offer.index', ['company' => $chosenUser->getCompany()->getId()]);
            } 

            $this->addFlash(type: 'warning', message: 'Veuillez choisir une image.');

            $form->getData()->getCompany()->setImageFile(null);
        }

        return $this->render('pages/user/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * This controller makes a request to delete a user account 
     * @param User $chosenUser
     * @param Request $request
     * @return Response
     */
    #[Route('/delete/{id}', name: 'delete', methods: ['GET', 'POST'])]
    public function deleteAccount(User $chosenUser, Request $request): Response
    {
        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        $chosenUser->countryEncode();

        $form = $this->createForm(UserType::class, $chosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $chosenUser->setIsDeleted(!$chosenUser->isIsDeleted());

            $chosenUser->setDescription($chosenUser->isIsDeleted() ? $chosenUser->getDescription() : null);
            
            $chosenUser->countryDecode(); //Convertis les initiales du pays en son nom complet.

            if ($chosenUser->isIsDeleted()) $this->eventDispatcher->dispatch(new UserDeleteEvent($chosenUser));

            $this->userRepository->save($chosenUser, true);

            $this->addFlash(
                type: 'success',
                message: 'La demande de suppression de votre compte à été ' . ($chosenUser->isIsDeleted() ? 'éffectuer' : 'annuler') . ' avec succes.'
            );

            return $this->redirectToRoute('offer.index', ['company' => $chosenUser->getCompany()->getId()]);
        }

        return $this->render('pages/user/delete_account.html.twig', ['form' => $form]);
    }
}
