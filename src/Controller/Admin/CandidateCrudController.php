<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Candidate;
use App\Form\CandidateAdminType;
use App\Repository\CandidateRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


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
        return static::addOrUpdate( $candidate,  $request);
    }

    /**
     * This controller update candidate
     * @param Candidate $candidate
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    #[Route('/{candidate}/update', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Candidate $candidate, Request $request): Response
    {
        return static::addOrUpdate( $candidate,  $request);
    }

    /**
     * This controller delete candidate
     * @param Candidate $candidate
     * @param Offer $offer
     * @param Request $request
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    #[Route('/{candidate}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Candidate $candidate, Request $request, UserPasswordHasherInterface $hasher): RedirectResponse
    {
        $count = (int)$request->query->get('count'); //Nombres de candidats sur la page courante
        $page = (int)$request->query->get('page'); //page courante
        $paginationCount = (int)$request->query->get('paginationCount'); //Nombres de candidats de la page de pagination
        $redirect = htmlspecialchars($request->query->get('redirect'));

        $passwordAndTokenValid = $hasher->isPasswordValid($this->getUser(), $request->request->get('_password')) && $this->isCsrfTokenValid('delete' . $candidate->getId(), $request->request->get('_token')); //user password and token valids

        if ($passwordAndTokenValid) {

            $this->candidateRepository->remove($candidate, true);
            $count--; //Nombres de candidats sur la page courante moins le candidat supprimer
            $paginationCount--; //Nombres de candidats sur la page de pagination moins le candidat supprimer
            $this->addFlash(type: 'success', message: 'Le candidat à été supprimer avec succès.');

            if ($count > 0) {
                return $this->redirectToRoute('admin.candidate.show', [
                    'page'      => $page,
                    'redirect'  => $redirect,
                    'count'     => $paginationCount,
                    'candidate' => $candidate->getId(),
                ]);
            }

            return $this->redirectToRoute($redirect, [
                'offer' => $candidate->getOffer()->getId(),
                'id'    => $candidate->getOffer()->getCompany()->getId(),
                'page'  => ($paginationCount > 0 && $page >= 2) || $page === 1 ? $page : $page - 1
            ]);
        } else {

            $this->addFlash(type: 'warning', message: 'Mot de passe et ou token invalide.');

            return $this->redirectToRoute('admin.candidate.show', [
                'page'      => $page,
                'redirect'  => $redirect,
                'count'     => $paginationCount,
                'candidate' => $candidate->getId(),
            ]);
        }
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
     * @param Offer $offer
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

                return $this->redirectToRoute('admin.offer.show', [
                    'offer' => $candidate->getOffer()->getId(),
                ]);
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'      => $form->createView(),
            'offer'     => $candidate->getOffer(),
            'imageName' => $candidate->getImageName(),
        ]);
    }
}
