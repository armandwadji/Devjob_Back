<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Company;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Symfony\Component\Intl\Countries;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin.society.')]
class CompanyCrudController extends  AbstractController
{

    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * This controller show all companies
     * @param UserRepository $userRepository
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

        return $this->render('pages/admin/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * This coontroller add company
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/society/new', name: 'new', methods: ['GET', 'POST'])]
    public function add(Request $request, SessionInterface $session): Response
    {
        $user = new User();
        return static::addOrUpdate($user, $request, $session);
    }

    /**
     * This controller show company detail
     * @param Company $company
     * @return Response
     */
    #[Route('/society/{name}', name: 'show', methods: ['GET'])]
    public function show(Company $company): Response
    {
        return $this->render('pages/user/account.html.twig', [
            'user' => $company->getUser(),
        ]);
    }

    /**
     * This controller edit company
     * @param Company $company
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/society/{name}/update/', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Company $company, Request $request, SessionInterface $session): Response
    {
        $user = $company->getUser();
        return static::addOrUpdate($user, $request, $session);
    }

    /**
     * This controller delete company
     * @param Company $company
     * @param Request $request
     * @param SessionInterface $session
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/society/{name}/delete/', name: 'delete', methods: ['POST'])]
    public function delete(Company $company, Request $request, SessionInterface $session): Response
    {
        $OffersCountPage = intval($request->query->get('count')) - 1; //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = intval(htmlspecialchars($session->get('page'))); //numéro de la page courante

        $user = $company->getUser();

        if ($user && $this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token')) ) {
            $this->userRepository->remove($user, true);
            $this->addFlash( type: 'success', message : 'La société ' . strtoupper($company->getName()) . ' à été supprimer avec succès.');
        }else{
            $this->addFlash( type: 'warning', message : 'La société ' . strtoupper($company->getName()) . ' n\'à pas pu être supprimer.');
        }

        return $this->redirectToRoute('admin.society.index', [
            'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1,
        ]);
    }

    /**
     * This method add or update company
     * @param User $user
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    private function addOrUpdate(User $user, Request $request, SessionInterface $session): Response
    {
        // GESTION DES CODES ISO POUR LA CONFORMITE DU FORMULAIRE
        if($user->getCompany() !== null){
            static::countryEncode($user);
        }

        $form = $this->createForm(RegistrationType::class, $user);
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

                $this->userRepository->save($user, true);
                $user->getCompany()->setImageFile(null);

                $this->addFlash(
                    type    : 'success',
                    message : 'L\'entreprise ' . strtoupper($user->getCompany()->getName()) . ' à bien été créer.'
                );

                // GESTION DE LA PAGINATION:
                $offersTotalCount = isset($_GET['count']) ? intval($request->get('count')) + 1 : null; //Nombres d'offer sur la page courante

                return $this->redirectToRoute('admin.society.index', [
                    'page'  => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)
                ]);
            }
        }

        return $this->render('pages/security/registration.html.twig', [
            'form'      => $form->createView(),
            'editMode'  => $user->getId(),
        ]);
    }

    private function countryEncode(User $user)
    {
        $isoCode2 = array_search($user->getCompany()->getCountry(), Countries::getNames(), true);
        $isoCode3 = Countries::getAlpha3Code($isoCode2);
        $user->getCompany()->setCountry($isoCode3);
    }
}
