<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Company;

use App\Form\OfferType;
use App\Repository\OfferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/offers', name: 'admin.offer.')]
class OfferCrudController extends AbstractController
{

    public function __construct(
        private OfferRepository $offerRepository,
    ) {
    }

    /**
     * This controller show all offers
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function offers( PaginatorInterface $paginator, Request $request, SessionInterface $session): Response
    {
        $session->set('page', isset($_GET['page']) ? intval($request->get('page'))  : 1);

        $offers = $paginator->paginate(
            target  : $this->offerRepository->findAll(),
            page    : $request->query->getInt('page', 1),
            limit   : 10
        );

        return $this->render('pages/offer/index.html.twig', [
            'offers' => $offers
        ]);
    }

    /**
     * This controller add offer
     * @param Company $company
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/new?{id}', name: 'new', methods: ['GET', 'POST'])]
    public function add(Company $company, Request $request, SessionInterface $session): Response
    {
        $offer = new Offer();
        $offer->setCompany($company);
        return static::addOrUpdate($offer, $request, $session);
    }

    /**
     * This controller show offer
     * @param Offer $offer
     * @return Response
     */
    #[Route('/{offer}', name: 'show', methods: ['GET'])]
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
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/{offer}/update', name: 'update', methods: ['GET', 'POST'])]
    public function edit(Offer $offer, Request $request, SessionInterface $session): Response
    {
        $isAllOffer = boolval($request->get('isAllOffer')) ; //Boolean permet de rediriger vers la liste de toutes les offres ou bien la liste des offres d'une entreprise
        return static::addOrUpdate($offer, $request, $session, $isAllOffer);
    }

    /**
     * This controller delete offer
     * @param Offer|null $offer
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route('/{offer}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Offer $offer = null,  Request $request, SessionInterface $session)
    {
        $OffersCountPage = intval($request->query->get('count')); //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = intval(htmlspecialchars($session->get('page'))); //numéro de la page courante
        $isAllOffer = boolval($request->get('isAllOffer')) ; //Boolean permet de rediriger vers la liste de toutes les offres ou bien la liste des offres d'une entreprise

        if ($offer && $this->isCsrfTokenValid('delete'.$offer->getId(), $request->request->get('_token'))) {
            $this->offerRepository->remove($offer, true);
            $this->addFlash( type :'success', message :'L\' offre à été supprimer avec succès.');
            $OffersCountPage--;
        }else{
            $this->addFlash( type: 'warning', message :'L\'offre demander n\'a pas pu être supprimé.');
        }

        if($isAllOffer){
            return $this->redirectToRoute('admin.offer.index', [
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
     * @param SessionInterface $session
     * @param bool|null $isAllOffer
     * @return Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function addOrUpdate(Offer $offer, Request $request, SessionInterface $session, ?bool $isAllOffer = false)
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
                    type    : 'success',
                    message : $offer->getId() ? 'L\' offre à été éditer avec succès!' : 'L\' offre à été créer avec succès!',
                );

                $this->offerRepository->save($offer, true);

                // CAS DE REDIRECTION VERS LA LISTE DE TOUTES LES OFFRES
                if($isAllOffer){
                    return $this->redirectToRoute('admin.offer.index', [
                        'page'  => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)
                    ]);
                }
                // CAS DE DERIRECTION VERS LA LISTE DES OFFRES D'UNE SOCIETE
                return $this->redirectToRoute('admin.society.show', [
                    'name'      => $offer->getCompany()->getName(),
                    'page'      => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)
                ]);
            }

            $this->addFlash(
                type    : 'warning',
                message : 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/offer/new_update_offer.html.twig', [
            'formOffer' => $form->createView(),
            'editMode'  => $offer->getId()
        ]);
    }
}
