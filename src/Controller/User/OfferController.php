<?php

namespace App\Controller\User;

use App\Controller\GlobalController;
use App\Entity\Offer;
use App\Entity\Company;
use App\Form\OfferType;
use App\Event\OfferDeleteEvent;
use App\Repository\OfferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/my-offers', name: 'offer.')]
class OfferController extends GlobalController
{
    public function __construct(
        private readonly OfferRepository $offerRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * This controller display all offers by company
     * @param Company $company
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('?{company}', name: 'index', methods: ['GET', 'POST'])]
    public function index(Company $company, PaginatorInterface $paginator, Request $request,  SessionInterface $session): Response
    {
        $session->set('page', isset($_GET['page']) ? (int)$request->get('page') : 1);

        $offers = $paginator->paginate(
            target  : $company->getOffer(),
            page    : $request->query->getInt('page', 1),
            limit   : 10
        );

        return $this->render('pages/offer/index.html.twig', ['offers' => $offers]);
    }

    /**
     * This controller create offer
     * @param Company $company
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/new?{company}', name: 'new', methods: ['GET', 'POST'])]
    public function new(Company $company, Request $request): Response
    {
        $offer = new Offer();
        $offer->setCompany($company);
        return static::newUpdate($offer, $request);
    }

    /**
     * This controller update offer
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/{offer}/update', name: 'edit', methods: ['GET', 'POST'])]
    public function update(Offer $offer, Request $request): Response
    {
        return static::newUpdate($offer, $request);
    }

    /**
     * This controller delete offer
     * @param Offer $offer
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/{offer}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Offer $offer,  Request $request,  UserPasswordHasherInterface $hasher): Response
    {
        static::pagination($request);

        if (static::isPassWordValid($hasher, $request, $offer)) {

            $this->offerRepository->remove($offer, true);

            $this->eventDispatcher->dispatch(new OfferDeleteEvent($offer));

            $this->setCount($this->getCount() - 1); //Nombres d'offres sur la page courante moins l'offre supprimer

            $this->addFlash(type: 'success', message: 'Votre offre à été supprimer avec succès!');

        } 

        return $this->redirectToRoute('offer.index', [
            'company'   => (int)$request->query->get('idCompany'),
            'page'      => $this->showDeletePage(),
        ]);
    }

    /**
     * This controller show detail of offer
     * @param Offer $offer
     * @return Response
     */
    #[Route('/{offer}', name: 'show', methods: ['GET'])]
    public function show(Offer $offer): Response
    {
        return $this->render('pages/offer/show.html.twig', ['offer' => $offer]);
    }

    /**
     * This controller create or update offer
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    private function newUpdate(Offer $offer, Request $request): Response
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
                    message: $offer->getId() ? "Votre offre à été éditer avec succès!" : 'Votre offre à été créer avec succès!',
                );

                return $this->redirectToRoute('offer.index', [
                    'company'   => $offer->getCompany()->getId(),
                    'page'      => $this->showAddEditPage(),
                ]);
            }

            $this->addFlash(type: 'warning', message: 'Veuillez bien saisir tous les champs!');
        }

        return $this->render('pages/offer/new_update_offer.html.twig', [
            'formOffer' => $form->createView(),
            'editMode'  => $offer->getId()
        ]);
    }
}
