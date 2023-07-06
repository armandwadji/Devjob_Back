<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Event\UserForgetPasswordEvent;
use App\Event\UserTokenRegistrationEvent;
use App\Form\RegistrationType;
use App\Service\MailerService;
use App\Repository\UserRepository;
use App\Form\UserChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/', name: 'security.')]
class SecurityController extends AbstractController
{

    public function __construct(
        private readonly MailerService   $mailerService,
        private readonly UserRepository  $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher
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

            $imageIsInvalid = $form->getData()->getCompany()->getImageFile() && !(bool) stristr($form->getData()->getCompany()->getImageFile()->getmimeType(), "image");

            if (!$imageIsInvalid) {

                // USER TOKEN REGISTRATION
                $user->setTokenRegistration($tokenGeneratorInterface->generateToken());

                $user->countryDecode();

                $this->userRepository->save($user, true);

                $this->eventDispatcher->dispatch(new UserTokenRegistrationEvent($user));
                
                $this->addFlash(type: 'success', message: 'Votre compte à bien été créer. veuillez vérifiez votre email pour l\'activé.');

                $user->getCompany()->setImageFile(null);

                return $this->redirectToRoute('security.login');
            } 

            $this->addFlash(type: 'warning', message: 'Veuillez choisir une image.');
            $form->getData()->getCompany()->setImageFile(null);
        }

        return $this->render('pages/security/registration.html.twig', [
            'form'      => $form->createView(),
            'editMode'  => false
        ]);
    }

    /**
     * This controller check if user is verified
     * @param string $token
     * @param User $user
     * @throws AccessDeniedException
     * @return Response
     */
    #[Route('/verify/{token}/{id}', name: 'verify', requirements: ['id' => '\d+'], methods: ['GET'])]
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

        $user->setIsVerified(true)->setTokenRegistration(null);

        $this->userRepository->save($user, true);
        
        $this->addFlash(type: 'success', message: 'Votre compte à bien été activé. vous pouvez maintenant vous connecté.');

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

            $emailIsValid = filter_var($request->get('email'), FILTER_VALIDATE_EMAIL);

            if (!$emailIsValid) {

                $this->addFlash(type: 'warning', message: 'Veuillez saisir un email valide.');
            }
            else {

                $user = $userRepository->findOneBy(['email' => $emailIsValid]);

                if (!$user) {
                    $this->addFlash(type: 'warning', message: 'Cette utilisateur n\'existe pas. veuillez vérifier votre email.');  
                } else {

                    // USER TOKEN
                    $user->setTokenRegistration($tokenGeneratorInterface->generateToken());

                    $this->userRepository->save($user, true);

                    $this->eventDispatcher->dispatch(new UserForgetPasswordEvent($user));
                    
                    $this->addFlash(type: 'success', message: 'Un email de réinitialisation de mots de passe vous à été envoyé. veuillez cliqué sur le lien pour changer votre mots de passe.');

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
    #[Route('/change/{token}/{id}', name: 'password.change', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
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

            $this->addFlash(type: 'success', message: 'Votre mots de passe à bien été modifié. Veuillez vous connecter avec vos nouveaux identifiants.');

            return $this->redirectToRoute('security.login');
        }

        return $this->render('pages/security/change_password_form.html.twig', ['form' => $form->createView()]);
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
