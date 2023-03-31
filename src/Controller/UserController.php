<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/my-account/update/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    public function edit(User $choosenUser = null, Request $request, EntityManagerInterface $manager,): Response
    {
        // Si nous n'avons pas d'utilsateur connecter on redirige vers la page de login.
        if (!$this->getUser()) return $this->redirectToRoute('security.login');

        $form = $this->createForm(UserType::class, $choosenUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // dd($form->getData()->getCompany()->getImageFile());

            if ($form->getData()->getCompany()->getImageFile() && !(bool)stristr($form->getData()->getCompany()->getImageFile()->getmimeType(), "image")) {

                $this->addFlash(
                    type: 'warning',
                    message: 'Veuillez choisir une image.'
                );

                $form->getData()->getCompany()->setImageFile(null);
            } else {

                // $file = $form->getData()->getCompany()->getImageFile()->getmimeType();

                // if ($hasher->isPasswordValid($choosenUser, $form->getData()->getPlainPassword())) {

                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
                $user->getCompany()->setImageFile(null);

                $this->addFlash(
                    type: 'success',
                    message: 'les informations de votre compte ont bien été modifiées.'
                );

                return $this->redirectToRoute('offer.index', ['id' => $user->getCompany()->getId()]);

                // } else {
                //     $this->addFlash(type: 'warning', message: 'le mot de passe renseigné est incorrect;');
                // }

            }
        }

        return $this->render('pages/user/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/my-account/{id}', name: 'account.index', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER') and user === choosenUser")]
    public function home(User $choosenUser = null): Response
    {
        return $this->render('pages/user/account.html.twig');
    }
}
