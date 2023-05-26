<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Service\MailerService;
use App\Repository\UserRepository;
use App\Form\UserChangePasswordType;
use Symfony\Component\Intl\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/', name: 'security.')]
class SecurityController extends AbstractController
{

    public function __construct(
        private UserRepository $userRepository,
        private MailerService $mailerService
    ) {
    }

    /**
     * this controller allow to register
     * @param Request $request
     * @param TokenGeneratorInterface $tokenGeneratorInterface
     * @return Response
     */
    #[Route('/register', name: 'registration', methods: ['GET', 'POST'])]
    public function registration(Request $request, TokenGeneratorInterface $tokenGeneratorInterface): Response
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

                // USER TOKEN
                $tokenRegistration = $tokenGeneratorInterface->generateToken();
                $user->setTokenRegistration($tokenRegistration);
                $user->getCompany()->setCountry(Countries::getAlpha3Name($user->getCompany()->getCountry())); //Convertis les initiales du pays en son nom complet.


                $this->userRepository->save($user, true);
                $user->getCompany()->setImageFile(null);

                // MAILER SEND
                $this->mailerService->send(
                    $user->getEmail(),
                    'Confirmation du compte utilisateur',
                    'registration_confirmation.html.twig',
                    [
                        'user'          => $user,
                        'token'         => $tokenRegistration,
                        'lifeTimetoken' => $user->getTokenRegistrationLifeTime()->format('d/m/Y à H:i:s')
                    ]
                );

                $this->addFlash(
                    type: 'success',
                    message: 'Votre compte à bien été créer. veuillez vérifiez votre email pour l\'activé.'
                );

                return $this->redirectToRoute('security.login');
            }
        }

        return $this->render('pages/security/registration.html.twig', [
            'form'      => $form->createView(),
            'editMode'  => false
        ]);
    }

    /**
     * This controller check if user is verify
     * @param string $token
     * @param User $user
     * @throws AccessDeniedException
     * @return Response
     */
    #[Route('/verify/{token}/{id<\d+>}', name: 'verify', methods: ['GET'])]
    public function emailVerify(string $token, User $user): Response
    {
        // IF TOKEN USER IS NOT SAME PASS IN PARAMETER
        if ($user->getTokenRegistration() !== $token) {
            throw new AccessDeniedException();
        }

        // IF THE USER IS ALREADY VERIFIED
        if ($user->getTokenRegistration() === null) {
            throw new AccessDeniedException();
        }

        // IF THE ACCOUNT CONFIRMATION DATE HAS ALREADY EXPIRED
        if (new \DateTimeImmutable('now') > $user->getTokenRegistrationLifeTime()) {
            throw new AccessDeniedException();
        }

        $user->setIsVerified(true)
            ->setTokenRegistration(null);

        $this->userRepository->save($user, true);

        $this->addFlash(
            type: 'success',
            message: 'Votre compte à bien été activé. vous pouvez maintenant vous connecté.'
        );

        return $this->redirectToRoute('security.login');
    }

    /**
     * this controller sends emails to reset a password
     * @param Request $request
     * @param UserRepository $userRepository
     * @param TokenGeneratorInterface $tokenGeneratorInterface
     * @return Response
     */
    #[Route('/forget', name: 'password.forget', methods: ['GET', 'POST'])]
    public function passwordForget(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGeneratorInterface): Response
    {
        if ($request->getMethod() === 'POST') {

            if (!filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {

                $this->addFlash(
                    type: 'warning',
                    message: 'Veuillez saisir un email valide.'
                );
            } else {

                $email = filter_var($request->get('email'), FILTER_VALIDATE_EMAIL);
                $user = $userRepository->findOneBy(['email' => $email]);

                if (!$user) {
                    $this->addFlash(
                        type: 'warning',
                        message: 'Cette utilisateur n\'existe pas. veuillez vérifier votre email.'
                    );
                } else {
                    // USER TOKEN
                    $tokenRegistration = $tokenGeneratorInterface->generateToken();
                    $user->setTokenRegistration($tokenRegistration);
                    $this->userRepository->save($user, true);

                    // MAILER SEND
                    $this->mailerService->send(
                        $user->getEmail(),
                        'Modiffication mots de passe utilisateur',
                        'change_password_email.html.twig',
                        [
                            'user'  => $user,
                            'token' => $tokenRegistration,
                        ]
                    );

                    $this->addFlash(
                        type: 'success',
                        message: 'Un email de réinitialisation de mots de passe vous à été envoyé. veuillez cliqué sur le lien pour changer votre mots de passe.'
                    );

                    return $this->redirectToRoute('security.login');
                }
            }
        }

        return $this->render('pages/user/password_forget.html.twig');
    }

    /**
     * this controller allows you to change passwords 
     * @param string $token
     * @param User $user
     * @param Request $request
     * @throws AccessDeniedException
     * @return Response
     */
    #[Route('/change/{token}/{id<\d+>}', name: 'password.change', methods: ['GET', 'POST'])]
    public function changePassword(string $token, User $user, Request $request): Response
    {
        if ($user->getTokenRegistration() !== $token) {
            throw new AccessDeniedException();
        }

        if ($user->getTokenRegistration() === null) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(UserChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setTokenRegistration(null);

            $this->userRepository->save($user, true);

            $this->addFlash(
                type: 'success',
                message: 'Votre mots de passe à bien été modifié. Veuillez vous connecter avec vos nouveaux identifiants.'
            );

            return $this->redirectToRoute('security.login');
        }

        return $this->render('pages/security/change_password_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This controller allow us to login
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
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
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout()
    {
        //Nothting to do here..
    }
}
