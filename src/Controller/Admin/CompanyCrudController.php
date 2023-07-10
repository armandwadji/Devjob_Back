<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Company;

use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Controller\GlobalController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin', name: 'admin.society.')]
class CompanyCrudController extends  GlobalController
{

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * This controller show all companies
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/society', name: 'index', methods: ['GET'])]
    public function index( PaginatorInterface $paginator, Request $request, SessionInterface $session ): Response
    {
        $session->set('page', isset($_GET['page']) ? (int)htmlspecialchars($_GET['page'])  : 1);

        $users = $paginator->paginate(
            target  : $this->userRepository->findUserNotAdmin(), //Méthode permétant d'aller récupérer tous les users qui sont pas administrateurs
            page    : $request->query->getInt('page', 1),
            limit   : 10
        );

        return $this->render('pages/admin/index.html.twig', ['users' => $users]);
    }

    /**
     * This coontroller add company
     * @param Request $request
     * @return Response
     */
    #[Route('/society/new', name: 'new', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $user = new User();
        return static::addOrUpdate($user, $request);
    }

    /**
     * This controller show company detail
     * @param Company $company
     * @param PaginatorInterface $paginator
     * @param  Request $request
     * @return Response
     */
    #[Route('/society/{name}', name: 'show', methods: ['GET'])]
    public function show(Company $company, PaginatorInterface $paginator, Request $request): Response
    {
        $offers = $paginator->paginate(
            target  : $company->getOffer(), //Méthode permétant d'aller récupérer tous les users qui sont pas administrateurs
            page    : $request->query->getInt('page', 1),
            limit   : 10
        );

        return $this->render('pages/admin/company_show.html.twig', ['user' => $company->getUser(), 'offers' => $offers]);
    }

    /**
     * This controller edit company
     * @param Company $company
     * @param Request $request
     * @return Response
     */
    #[Route('/society/{name}/update/', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Company $company, Request $request): Response
    {
        $user = $company->getUser();
        return static::addOrUpdate($user, $request);
    }

    /**
     * This controller delete company
     * @param Company $company
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/society/{name}/delete/', name: 'delete', methods: ['POST'])]
    public function delete(Company $company, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        static::pagination($request);

        if (static::isPassWordValid($hasher, $request, $company)) {

            $this->userRepository->remove($company->getUser(), true);

            $this->setCount($this->getCount() - 1); //Nombres d'entreprises sur la page courante moins l'entreprise supprimer

            $this->addFlash( type: 'success', message : 'La société ' . strtoupper($company->getName()) . ' à été supprimer avec succès.');
        }

        return $this->redirectToRoute('admin.society.index', ['page' => $this->showDeletePage()]);
    }

    /**
     * This method add or update company
     * @param User $user
     * @param Request $request
     * @return Response|RedirectResponse
     */
    private function addOrUpdate(User $user, Request $request): Response|RedirectResponse
    {
        // GESTION DES CODES ISO POUR LA CONFORMITE DU FORMULAIRE
        $user->getCompany()->countryEncode();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageIsInvalid = $user->getCompany()->getImageFile() && !(bool) stristr($user->getCompany()->getImageFile()->getmimeType(), "image");


            if (!$imageIsInvalid) {

                $user->getCompany()->countryDecode(); //Convertis les initiales du pays en son nom complet.
                
                $this->userRepository->save($user, true);

                $user->getCompany()->setImageFile(null);
                
                // GESTION DE LA PAGINATION:
                static::pagination($request);
                if ($this->getCount()) $this->setCount($this->getCount() + 1); //On Ajoute une entreprise                

                $this->addFlash(
                    type    : 'success',
                    message : 'L\'entreprise ' . strtoupper($user->getCompany()->getName()) . ' à bien été' . ($user->getId() ? ' éditer.' : ' créer.')
                );

                return $this->redirectToRoute('admin.society.index', [
                    'page'  => $this->showAddEditPage(),
                ]);
            } 

            $this->addFlash(type: 'warning', message: 'Veuillez choisir une image.');
            $user->getCompany()->setImageFile(null);
        }

        return $this->render('pages/security/registration.html.twig', [
            'form'      => $form->createView(),
        ]);
    }

}
