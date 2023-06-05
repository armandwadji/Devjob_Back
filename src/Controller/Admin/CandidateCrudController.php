<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Candidate;
use App\Form\CandidateAdminType;
use App\Repository\CandidateRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('admin/applicants', name: 'admin.candidate.')]
class CandidateCrudController extends AbstractController
{
    public function __construct(
        private CandidateRepository $candidateRepository,
    ) {
    }

    /**
     * This controller add candidate
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    #[Route('/new?{offer}', name: 'new', methods: ['GET', 'POST'])]
    public function add(Offer $offer,  Request $request): Response
    {
        $candidate = new Candidate();
        $candidate->setOffer($offer);
        return static::addOrUpdate($offer, $candidate,  $request);
    }

    /**
     * This controller update candidate
     * @param Candidate $candidate
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    #[Route('/{candidate}/update?{offer}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Candidate $candidate, Offer $offer, Request $request): Response
    {
        return static::addOrUpdate($offer, $candidate,  $request);
    }

    /**
     * This controller delete candidate
     * @param Candidate $candidate
     * @param Offer $offer
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/{candidate}/delete/{offer}', name: 'delete', methods: ['POST'])]
    public function delete(Candidate $candidate, Offer $offer, Request $request, SessionInterface $session): Response
    {
        $OffersCountPage = intval($request->query->get('count')) - 1; //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = intval(htmlspecialchars($session->get('page'))); //numéro de la page courante

        if ($candidate && $this->isCsrfTokenValid('delete'.$candidate->getId(), $request->request->get('_token'))) {
            $this->candidateRepository->remove($candidate, true);
            $this->addFlash( type: 'success' , message: 'Le candidat à été supprimer avec succès.' );
        }else{
            $this->addFlash( type: 'warning', message: 'Le candidat n\'a pas pu être supprimer.' );
        }

        return $this->redirectToRoute('admin.offer.show', [
            'offer' => $offer->getId(),
            'id'    => intval($request->query->get('idCompany')),
            'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1
        ]);
    }

    /**
     * This controller show detail of candidate
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    #[Route('/{candidate}', name: 'show', methods: ['GET'])]
    public function show (Candidate $candidate, Request $request): Response
    {
        return $this->render('pages/candidate/show.html.twig', [
            'candidate'             => $candidate,
            'offer'                 => intval($request->query->get('offer')),
            'candidatesForOffer'    => intval($request->query->get('count')),
            'page'                  => intval($request->query->get('page')),
            'isAdmin'               => true
        ]);
    }

    /**
     * This controller ad or edit candidate
     * @param Offer $offer
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    private function addOrUpdate(Offer $offer, Candidate $candidate, Request $request ): Response
    {
        $form = $this->createForm(CandidateAdminType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && ($form->getData()->getImageFile() || $form->getData()->getImageName())) {

                $this->addFlash(
                    type    : 'success',
                    message : $candidate->getId() ? "La candidature à été modifer avec succès" : "La candidature à été ajouter avec succès !"
                );

                $this->candidateRepository->save($candidate, true);

                return $this->redirectToRoute('admin.offer.show', [
                    'offer' => $offer->getId(),
                ]);
            }

            $this->addFlash(
                type    : 'warning',
                message : 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'      => $form->createView(),
            'offer'     => $offer,
            'imageName' => $candidate->getImageName(),
        ]);
    }
}
