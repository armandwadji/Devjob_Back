<?php

namespace App\Controller\Admin;

use App\Controller\GlobalController;
use App\Entity\Offer;
use App\Entity\Company;

use App\Form\OfferType;
use App\Repository\OfferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/offers', name: 'admin.offer.')]
class OfferCrudController extends GlobalController
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
    public function offers(PaginatorInterface $paginator, Request $request, SessionInterface $session): Response
    {
        $session->set('page', isset($_GET['page']) ? intval($request->get('page'))  : 1);

        $offers = $paginator->paginate(
            target: $this->offerRepository->findAll(),
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
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/new?{id}', name: 'new', methods: ['GET', 'POST'])]
    public function add(Company $company, Request $request): Response
    {
        $offer = new Offer();
        $offer->setCompany($company);
        return static::addOrUpdate($offer, $request);
    }

    /**
     * This controller show offer
     * @param Offer $offer
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/{offer}', name: 'show', methods: ['GET'])]
    public function show(Offer $offer, PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = $paginator->paginate(
            target: $offer->getCandidates(),
            page: $request->query->getInt('page', 1),
            limit: 10
        );

        return $this->render('pages/admin/offer_show.html.twig', [
            'offer' => $offer,
            'candidates' => $candidates,
        ]);
    }

    /**
     * This controller edit offer
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    #[Route('/{offer}/update', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Offer $offer, Request $request): Response
    {
        return static::addOrUpdate($offer, $request);
    }

    /**
     * This controller delete offer
     * @param Offer $offer
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return RedirectResponse
     */
    #[Route('/{offer}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Offer $offer,  Request $request, UserPasswordHasherInterface $hasher): RedirectResponse
    {
        static::pagination($request);

        if (static::isPassWordValid($hasher, $request, $offer)) {

            $this->offerRepository->remove($offer, true);

            $this->setCount($this->getCount() - 1); //Nombres d'offres sur la page courante moins l'offre supprimer

            $this->addFlash(type: 'success', message: 'L\' offre à été supprimer avec succès.');
        }

        return $this->redirectToRoute($this->getRedirect(), [
            'name'  => $offer->getCompany()->getName(),
            'page'  => $this->showDeletePage(),
        ]);
    }

    /**
     * This controller add or update offer
     * @param Offer $offer
     * @param Request $request
     * @return Response|RedirectResponse
     */
    private function addOrUpdate(Offer $offer, Request $request): Response|RedirectResponse
    {
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                foreach ($offer->getRequirement()->getRequirementItems() as $requirementItem) {

                    if (!$requirementItem->getRequirement()) $requirementItem->setRequirement($offer->getRequirement());
                }

                foreach ($offer->getRole()->getRoleItems() as $roleItem) {

                    if (!$roleItem->getRole()) $roleItem->setRole($offer->getRole());
                }

                // GESTION DE LA PAGINATION:
                static::pagination($request);

                $this->offerRepository->save($offer, true);

                if ($this->getCount()) $this->setCount($this->getCount() + 1); //On Ajoute une offre
                
                $this->addFlash(
                    type: 'success',
                    message: $offer->getId() ? 'L\' offre à été éditer avec succès!' : 'L\' offre à été créer avec succès!',
                );

                return $this->redirectToRoute($this->getRedirect(), [
                    'name'      => $offer->getCompany()->getName(),
                    'page'      => $this->showAddEditPage(),
                ]);
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/offer/new_update_offer.html.twig', [
            'formOffer' => $form->createView(),
        ]);
    }
}
