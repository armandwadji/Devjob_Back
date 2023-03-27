<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Form\OfferType;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferController extends AbstractController
{

    /**
     * This controller display all offers with pagination system
     * @param OfferRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/my-offers', name: 'offer.index', methods: ['GET'])]
    public function index(OfferRepository $repository, PaginatorInterface $paginator, Request $request,  SessionInterface $session): Response
    {
        $session->set('page', isset($_GET['page']) ? (int)htmlspecialchars($_GET['page'])  : 1);

        $offers = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    /**
     * This controller create offer
     * @param Offer|null $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/my-offers/new', name: 'offer.new', methods: ['GET', 'POST'])]
    public function new(Offer $offer = null,  Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $offer = new Offer();
        return static::newUpdate($offer, $request, $manager, $session);
    }

    /**
     * This controller update offer
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/my-offers/{id}/update', name: 'offer.edit', methods: ['GET', 'POST'])]
    public function update(Offer $offer,  Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        return static::newUpdate($offer, $request, $manager, $session);
    }

    /**
     * This method create or update offer
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    private function newUpdate(Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $offer =  $form->getData();
                //$offer->setUser($this->getUser());

                // GESTION DE LA PAGINATION:
                $offersTotalCount = isset($_GET['count']) ? (int) htmlspecialchars($_GET['count']) + 1 : null; //Nombres de recettes sur la page courante

                $this->addFlash(
                    type: 'success',
                    message: $offer->getId() ? "Votre offre à été éditer avec succès!" : 'Votre offre à été créer avec succès!'
                );

                $manager->persist($offer);
                $manager->flush();

                return $this->redirectToRoute('offer.index', ['page' => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)]);
            }

            $this->addFlash(type: 'warning', message: 'Veuillez bien saisir tous les champs!');
        }

        return $this->render('pages/offer/new_update_offer.html.twig', [
            'formOffer' => $form->createView(),
            'editMode' => $offer->getId()
        ]);
    }

    /**
     * this controller delete offer
     * @param Offer|null $offer
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/my-offers/{id}/delete', name: 'offer.delete', methods: ['GET'])]
    public function delete(Offer $offer = null, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $OffersCountPage = (int) htmlspecialchars($_GET['count']) - 1; //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = (int)htmlspecialchars($session->get('page')); //numéro de la page courante

        if ($offer) {
            $manager->remove($offer);
            $manager->flush();
        }

        $this->addFlash(
            type: $offer ? 'success' : 'warning',
            message: $offer ? 'Votre offre à été supprimer avec succès!' : 'L\'offre demander n\'existe pas'
        );

        return $this->redirectToRoute('offer.index', ['page' => $OffersCountPage >= 1 ?  $page : $page - 1]);
    }
}
