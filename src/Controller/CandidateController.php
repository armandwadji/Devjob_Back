<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Company;
use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CandidateController extends AbstractController
{

    /**
     * This controller show all candidates by offer
     * @param Offer $offer
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/offers/{id}/applicants', name: 'offer.candidates.show', methods: ['GET'])]
    public function candidatesByOffer(Offer $offer, PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = $paginator->paginate(
            target: $offer->getCandidates(),
            page: $request->query->getInt('page', 1),
            limit: 5
        );

        return $this->render('pages/candidate/candidates_by_offer.html.twig', [
            'offer'         => $offer,
            'candidates'    => $candidates,
        ]);
    }

    /**
     * This controller show all candidates by company
     * @param Company $company
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/my-applicants?{id}', name: 'offer.all.candidates.show', methods: ['GET'])]
    public function candidatesByCompany(Company $company, PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = [];
        foreach ($company->getOffer() as $offer) {
            foreach ($offer->getCandidates() as $candidat) {
                $candidates[] = $candidat;
            }
        }

        $candidates = $paginator->paginate(
            target: $candidates,
            page: $request->query->getInt('page', 1),
            limit: 5
        );

        return $this->render('pages/candidate/candidates_by_company.html.twig', [
            'candidates'    => $candidates,
            'offer'         => $offer
        ]);
    }

    /**
     * This controller displays the details of a candidate
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== candidate.getOffer()[0].getCompany().getUser()")]
    #[Route('/my-applicants/{id}', name: 'candidate.show', methods: ['GET'])]
    public function candidat(Candidate $candidate, Request $request): Response
    {
        return $this->render('pages/candidate/show.html.twig', [
            'candidate'             => $candidate,
            'offer'                 => intval($request->query->get('offer')),
            'candidatesForOffer'    => intval($request->query->get('candidates')),
            'page'                  => intval($request->query->get('page'))
        ]);
    }

    /**
     * This controller delete candidat
     * @param Candidate|null $candidate
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== candidate.getOffer()[0].getCompany().getUser()")]
    #[Route('/my-applicants/{id}/delete', name: 'candidate.delete', methods: ['GET'])]
    public function delete(Candidate $candidate = null, EntityManagerInterface $manager, Request $request): Response
    {
        $OffersCountPage = intval($request->query->get('count'))  - 1; //Nombres de candidats sur la page courante moins le candidat à supprimer
        $page = intval($request->query->get('page')); //numéro de la page courante

        $companyId = $candidate->getOffer()[0]->getCompany()->getId();

        if ($candidate) {
            $manager->remove($candidate);
            $manager->flush();
        }

        $this->addFlash(
            type: $candidate ? 'success' : 'warning',
            message: $candidate ? 'Le candidat à été supprimer avec succès!' : 'Le candidat demander n\'existe pas'
        );


        if ($OffersCountPage === 0 && $page === 1) {
            return $this->redirectToRoute('offer.index', ['id' => $companyId]);
        }

        return $this->redirectToRoute('offer.candidates.show', [
            'id'    => intval($request->query->get('idOffer')),
            'page'  => ($OffersCountPage > 0 && $page >= 2) || $page === 1 || $page === 0 ?  $page  : $page - 1
        ]);
    }

    /**
     * This controller allows a candidate to apply for an offer
     * @param Candidate|null $candidate
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/offers/{id}/apply', name: 'candidate.apply', methods: ['GET', 'POST'])]
    public function apply(Candidate $candidate = null, Offer $offer, Request $request, EntityManagerInterface $manager): Response
    {
        $candidate = new Candidate();
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && ($form->getData()->getImageFile() || $form->getData()->getImageName()) ) {

                $candidate = $form->getData();
                $candidate->addOffer($offer);

                $this->addFlash(
                    type: 'success',
                    message: "Votre candidature à été envoyer avec succès !"
                );

                $manager->persist($candidate);
                $manager->flush();

                return $this->redirectToRoute('home.index');
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'  => $form->createView(),
            'offer' => $offer
        ]);
    }
}
