<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
        // Si nous n'avons pas d'utilsateur connecter on redirige vers la page de login.
        if (!$this->getUser()) return $this->redirectToRoute('security.login');

        // GESTION DES CODES ISO POUR LA CONFOMITE DU FORMULAIRE
        $isoCode2 = array_search($choosenUser->getCompany()->getCountry(), Countries::getNames(), true);
        $isoCode3 = Countries::getAlpha3Code($isoCode2);
        $choosenUser->getCompany()->setCountry($isoCode3);

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

                $user = $form->getData();
                $user->getCompany()->setCountry(Countries::getAlpha3Name($user->getCompany()->getCountry()) ); //Convertis les initiales du pays en son nom complet.

                $manager->persist($user);
                $manager->flush();
                $user->getCompany()->setImageFile(null);

                $this->addFlash(
                    type: 'success',
                    message: 'Les informations de votre compte ont bien été modifiées.'
                );

                return $this->redirectToRoute('offer.index', [
                    'id' => $user->getCompany()->getId(),
                ]);
            }
        }

        return $this->render('pages/user/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This controller display detail of company
     * @param User|null $choosenUser
     * @return Response
     */
    #[Route('/my-account/{id}', name: 'account.index', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    public function home(User $choosenUser): Response
    {
        return $this->render('pages/user/account.html.twig', [
            'user' => $choosenUser,
        ]);
    }
}
