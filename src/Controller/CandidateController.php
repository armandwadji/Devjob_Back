<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Company;
use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
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
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/my-offers/{id}/applicants', name: 'offer.candidates.show', methods: ['GET'])]
    public function candidatesByOffer(Offer $offer, CandidateRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = $paginator->paginate(
            $repository->findBy(['offer' => $offer->getId()]),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('pages/candidate/candidates_by_offer.html.twig', [
            'offer' => $offer,
            'candidates' => $candidates,
        ]);
    }

    /**
     * This controller show all candidates by company
     * @param Company $company
     * @param CandidateRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/my-applicants/{id}', name: 'offer.all.candidates.show', methods: ['GET'])]
    public function candidatesByCompany(Company $company, CandidateRepository $repository,  PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = $paginator->paginate(
            $repository->findCandidatesByUser($company->getId()),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/candidate/candidates.html.twig', [
            'candidates' => $candidates,
        ]);
    }

    #[Route('/myapplicants/{id}', name: 'candidat.show', methods: ['GET'])]
    public function candidat(Candidate $candidat): Response
    {
        // dd($candidat);
        return $this->render('pages/candidate/show.html.twig', [
            'candidat' => $candidat
        ]);
    }

    #[Route('/offers/{id}/apply', name: 'candidate.apply', methods: ['GET', 'POST'])]
    public function apply(Candidate $candidate = null, Offer $offer, Request $request, EntityManagerInterface $manager): Response
    {
        $candidate = new Candidate();
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                $candidate = $form->getData();
                $candidate->setOffer($offer);

                $this->addFlash(
                    type: 'success',
                    message: "Votre candidature à été envoyer avec succès !"
                );

                $manager->persist($candidate);
                $manager->flush();

                return $this->redirectToRoute('home.index');
            }

            $this->addFlash(type: 'warning', message: 'Veuillez bien saisir tous les champs!');
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form' => $form->createView(),
            'offer' => $offer
        ]);
    }
}
