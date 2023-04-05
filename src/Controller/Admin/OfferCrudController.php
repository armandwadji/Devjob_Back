<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Company;

use App\Form\OfferType;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferCrudController extends AbstractController
{
    /**
     * This controller show all offers
     * @param OfferRepository $offerRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/admin/offers/', name: 'admin.offers', methods: ['GET'])]
    public function offers(OfferRepository $offerRepository, PaginatorInterface $paginator, Request $request, SessionInterface $session): Response
    {
        $session->set('page', isset($_GET['page']) ? intval($request->get('page'))  : 1);

        $offers = $paginator->paginate(
            target: $offerRepository->findAll(),
            page: $request->query->getInt('page', 1),
            limit: 10
        );

        return $this->render('pages/offer/index.html.twig', [
            'offers' => $offers
        ]);
    }

    /**
     * This controller add offer
     * @param Company $company
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/admin/offers/new?{id}', name: 'admin.offer.new', methods: ['GET', 'POST'])]
    public function add(Company $company, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $offer = new Offer();
        $offer->setCompany($company);
        return static::addOrUpdate($offer, $request, $manager, $session);
    }

    /**
     * This controller show offer
     * @param Offer $offer
     * @return Response
     */
    #[Route('/admin/offers/{offer}', name: 'admin.offers.show', methods: ['GET'])]
    public function show(Offer $offer): Response
    {
        return $this->render('pages/offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }

    /**
     * This controller edit offer
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/admin/offers/{offer}/update', name: 'admin.offer.update', methods: ['GET', 'POST'])]
    public function edit(Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $isAllOffer = boolval($request->get('isAllOffer')) ; //Boolean permet de rediriger vers la liste de toutes les offres ou bien la liste des offres d'une entreprise
        return static::addOrUpdate($offer, $request, $manager, $session, $isAllOffer);
    }

    /**
     * This controller delete offer
     * @param Offer|null $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route('/admin/offers/{offer}/delete', name: 'admin.offer.delete', methods: ['GET', 'POST'])]
    public function delete(Offer $offer = null,  Request $request, EntityManagerInterface $manager, SessionInterface $session)
    {
        $OffersCountPage = intval($request->query->get('count')) - 1; //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = intval(htmlspecialchars($session->get('page'))); //numéro de la page courante
        $isAllOffer = boolval($request->get('isAllOffer')) ; //Boolean permet de rediriger vers la liste de toutes les offres ou bien la liste des offres d'une entreprise

        if ($offer) {
            $manager->remove($offer);
            $manager->flush();
        }

        $this->addFlash(
            type: $offer ? 'success' : 'warning',
            message: $offer ? 'L\' offre à été supprimer avec succès!' : 'L\'offre demander n\'existe pas'
        );

        
        if($isAllOffer){

            return $this->redirectToRoute('admin.offers', [
                'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1
            ]);
        }

        return $this->redirectToRoute('admin.society.show', [
            'name'  => $offer->getCompany()->getName(),
            'id'    => intval($request->query->get('idCompany')),
            'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1
        ]);
    }

    /**
     * This controller add or update offer
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @param bool|null $isAllOffer
     * @return Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function addOrUpdate(Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session, ?bool $isAllOffer = false)
    {
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $offer =  $form->getData();

                foreach ($offer->getRequirement()->getRequirementItems() as $requirementItem) {

                    if (!$requirementItem->getRequirement()) $requirementItem->setRequirement($offer->getRequirement());
                }

                foreach ($offer->getRole()->getRoleItems() as $roleItem) {

                    if (!$roleItem->getRole()) $roleItem->setRole($offer->getRole());
                }

                // GESTION DE LA PAGINATION:
                $offersTotalCount = isset($_GET['count']) ? intval($request->get('count')) + 1 : null; //Nombres de recettes sur la page courante

                $this->addFlash(
                    type: 'success',
                    message: $offer->getId() ? 'L\' offre à été éditer avec succès!' : 'L\' offre à été créer avec succès!',
                );

                $manager->persist($offer);
                $manager->flush();

                // CAS DE REDIRECTION VERS LA LISTE DE TOUTES LES OFFRES
                if($isAllOffer){
                    return $this->redirectToRoute('admin.offers', [
                        'page'  => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)
                    ]);
                }
                // CAS DE DERIRECTION VERS LA LISTE DES OFFRES D'UNE SOCIETE
                return $this->redirectToRoute('admin.society.show', [
                    'name'    => $offer->getCompany()->getName(),
                    'page'  => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)
                ]);
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/offer/new_update_offer.html.twig', [
            'formOffer' => $form->createView(),
            'editMode'  => $offer->getId()
        ]);
    }
}
