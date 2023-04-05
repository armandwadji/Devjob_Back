<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    /**
     * this controller allow to register
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/register', name: 'security.registration', methods: ['GET', 'POST'])]
    public function registration(Request $request, EntityManagerInterface $manager): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
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
                $manager->persist($user);
                $manager->flush();
                $user->getCompany()->setImageFile(null);

                $this->addFlash(
                    type: 'success',
                    message: 'Votre compte à bien été créer.'
                );

                return $this->redirectToRoute('security.login');
            }
        }

        return $this->render('pages/security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow us to login
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/login', name: 'security.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('pages/security/login.html.twig', [
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'lastUsername'  => $authenticationUtils->getLastUsername(),
        ]);
    }

    /**
     * This controller allow us to logout 
     * @return void
     */
    #[Route('/logout', name: 'security.logout', methods: ['GET'])]
    public function logout()
    {
        //Nothting to do here..
    }
}
