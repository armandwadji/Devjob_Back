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

class CompanyCrudController extends  AbstractController
{

    /**
     * This controller show all companies
     * @param UserRepository $userRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/admin', name: 'admin.index')]
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request, SessionInterface $session): Response
    {
        $session->set('page', isset($_GET['page']) ? (int)htmlspecialchars($_GET['page'])  : 1);

        $users = $paginator->paginate(
            target  : $userRepository->findUserNotAdmin(), //Méthode permétant d'aller récupérer tous les users qui sont pas administrateurs
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
    #[Route('/admin/society/new', name: 'admin.society.new')]
    public function add(Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $user = new User();
        return static::addOrUpdate($user, $request, $manager, $session);
    }

    /**
     * This controller show company detail
     * @param Company $company
     * @return Response
     */
    #[Route('/admin/society/{name}', name: 'admin.society.show', methods: ['GET'])]
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
    #[Route('/admin/society/{name}/update/', name: 'admin.society.edit', methods: ['GET', 'POST'])]
    public function edit(Company $company, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $user = $company->getUser();
        return static::addOrUpdate($user, $request, $manager, $session);
    }


    /**
     * This controller delete company
     * @param Company $company
     * @param Request $request
     * @param SessionInterface $session
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/society/{name}/delete/', name: 'admin.society.delete', methods: ['GET'])]
    public function delete(Company $company, Request $request, SessionInterface $session, EntityManagerInterface $manager): Response
    {
        $OffersCountPage = intval($request->query->get('count')) - 1; //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = intval(htmlspecialchars($session->get('page'))); //numéro de la page courante

        $user = $company->getUser();

        if ($user) {
            $manager->remove($user);
            $manager->flush();
        }

        $this->addFlash(
            type    : $user ? 'success' : 'warning',
            message : $user ? 'La société ' . strtoupper($company->getName()) . ' à été supprimer avec succès!' : 'La société demander n\'existe pas'
        );

        return $this->redirectToRoute('admin.index', [
            'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1,
        ]);
    }

    /**
     * This method add or update company
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    private function addOrUpdate(User $user, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
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
                $manager->persist($user);
                $manager->flush();
                $user->getCompany()->setImageFile(null);

                $this->addFlash(
                    type    : 'success',
                    message : 'L\'entreprise ' . strtoupper($user->getCompany()->getName()) . ' à bien été créer.'
                );

                // GESTION DE LA PAGINATION:
                $offersTotalCount = isset($_GET['count']) ? intval($request->get('count')) + 1 : null; //Nombres de recettes sur la page courante

                return $this->redirectToRoute('admin.index', [
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
