<?php

namespace App\Controller\Admin;

use App\Controller\GlobalController;
use App\Entity\Offer;
use App\Entity\Candidate;
use App\Form\CandidateAdminType;
use App\Repository\CandidateRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


#[Route('admin/applicants', name: 'admin.candidate.')]
class CandidateCrudController extends GlobalController
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
        return static::addOrUpdate($candidate,  $request);
    }

    /**
     * This controller update candidate
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    #[Route('/{candidate}/update', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Candidate $candidate, Request $request): Response
    {
        return static::addOrUpdate($candidate,  $request);
    }

    /**
     * This controller delete candidate
     * @param Candidate $candidate
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return RedirectResponse
     */
    #[Route('/{candidate}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Candidate $candidate, Request $request, UserPasswordHasherInterface $hasher): RedirectResponse
    {
        // GESTION DE LA PAGINATION:
        static::pagination($request);

        $offersOfThisCandidate = (int)$request->query->get('offersOfThisCandidate'); //Nombres de candidats de la page de pagination

        if (static::isPassWordValid($hasher, $request, $candidate)) {

            $this->candidateRepository->remove($candidate, true);
            
            $offersOfThisCandidate--; //Nombres de candidatures sur la page courante moins la candidature supprimer
            
            $this->setCount($this->getCount() - 1); //Nombres de candidats sur la page de pagination moins le candidat supprimer
            
            $this->addFlash(type: 'success', message: 'Le candidat à été supprimer avec succès.');

            if ($offersOfThisCandidate === 0) {

                return $this->redirectToRoute($this->getRedirect() ?: 'admin.offer.show', [
                    'offer' => $candidate->getOffer()->getId(),
                    'id'    => $candidate->getOffer()->getCompany()->getId(),
                    'page'  => $this->showDeletePage(),
                ]);
            }
        }

        return $this->redirectToRoute('admin.candidate.show', [
            'page'      => $this->getPage(),
            'redirect'  => $this->getRedirect(),
            'count'     => $this->getCount(),
            'candidate' => $candidate->getId(),
        ]);
    }

    /**
     * This controller show detail of candidate
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    #[Route('/{candidate}', name: 'show', methods: ['GET'])]
    public function show(Candidate $candidate, Request $request): Response
    {
        return $this->render('pages/candidate/show.html.twig', [
            'count'         => (int)$request->query->get('count'),
            'page'          => (int)$request->query->get('page'),
            'redirect'      => htmlspecialchars($request->query->get('redirect')),
            'candidates'    => $this->candidateRepository->findBy(['email' => $candidate->getEmail()]),
        ]);
    }

    /**
     * This controller show all candidatures of one candidate
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'all', methods: ['GET'])]
    public function all(PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = $paginator->paginate(
            target: $this->candidateRepository->findCandidatesGroupByEmail(),
            page: $request->query->getInt('page', 1),
            limit: 10
        );

        return $this->render('pages/candidate/candidates_by_company.html.twig', [
            'candidates' => $candidates,
        ]);
    }

    /**
     * This controller ad or edit candidate
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    private function addOrUpdate(Candidate $candidate, Request $request): Response|RedirectResponse
    {
        $form = $this->createForm(CandidateAdminType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && ($form->getData()->getImageFile() || $form->getData()->getImageName())) {

                $this->candidateRepository->save($candidate, true);

                $this->addFlash(
                    type: 'success',
                    message: $candidate->getId() ? "La candidature à été modifer avec succès" : "La candidature à été ajouter avec succès !"
                );

                return $this->redirectToRoute('admin.candidate.show', [
                    'candidate' => $candidate->getId(),
                ]);
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'      => $form->createView(),
            'candidate' => $candidate,
        ]);
    }
}
