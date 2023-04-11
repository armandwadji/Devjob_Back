<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Candidate;
use App\Form\CandidateAdminType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CandidateCrudController extends AbstractController
{

    /**
     * This controller add candidate
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('admin/applicants/new?{offer}', name: 'admin.candidate.new', methods: ['GET', 'POST'])]
    public function add(Offer $offer,  Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $candidate = new Candidate();
        $candidate->setOffer($offer);
        return static::addOrUpdate($offer, $candidate,  $request, $manager, $session);
    }

    /**
     * This controller update candidate
     * @param Candidate $candidate
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('admin/applicants/{candidate}/update?{offer}', name: 'admin.candidate.edit', methods: ['GET', 'POST'])]
    public function edit(Candidate $candidate, Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        return static::addOrUpdate($offer, $candidate,  $request, $manager, $session);
    }

    /**
     * This controller delete candidate
     * @param Candidate $candidate
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('admin/applicants/{candidate}/delete?{offer}', name: 'admin.candidate.delete', methods: ['GET', 'POST'])]
    public function delete(Candidate $candidate, Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $OffersCountPage = intval($request->query->get('count')) - 1; //Nombres d'offres sur la page courante moins l'offre à supprimer
        $page = intval(htmlspecialchars($session->get('page'))); //numéro de la page courante

        if ($candidate) {
            $manager->remove($candidate);
            $manager->flush();
        }

        $this->addFlash(
            type: $candidate ? 'success' : 'warning',
            message: $candidate ? 'Le candidat à été supprimer avec succès!' : 'Le candidat demander n\'existe pas'
        );

        return $this->redirectToRoute('admin.offers.show', [
            'offer' => $offer->getId(),
            'id'    => intval($request->query->get('idCompany')),
            'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1
        ]);
    }

    #[Route('admin/applicant?{candidate}', name: 'admin.candidate.show', methods: ['GET', 'POST'])]
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
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    private function addOrUpdate(Offer $offer, Candidate $candidate, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $form = $this->createForm(CandidateAdminType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && ($form->getData()->getImageFile() || $form->getData()->getImageName())) {

                $candidate = $form->getData();

                $this->addFlash(
                    type: 'success',
                    message: $candidate->getId() ? "La candidature à été modifer avec succès" : "La candidature à été ajouter avec succès !"
                );

                $manager->persist($candidate);
                $manager->flush();

                return $this->redirectToRoute('admin.offers.show', [
                    'offer' => $offer->getId(),
                ]);
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'  => $form->createView(),
            'offer' => $offer,
            'imageName' => $candidate->getImageName(),
        ]);
    }
}
