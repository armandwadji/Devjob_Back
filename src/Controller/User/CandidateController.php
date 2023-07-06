<?php

namespace App\Controller\User;

use App\Controller\GlobalController;
use App\Entity\Offer;
use App\Entity\Company;
use App\Entity\Candidate;
use App\Form\CandidateType;
use App\Event\CandidateDeleteEvent;
use App\Repository\CandidateRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CandidateController extends GlobalController
{

    public function __construct(
        private readonly CandidateRepository $candidateRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * This controller show all candidates by offer
     * @param Offer $offer
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/offers/{offer}/applicants', name: 'offer.candidates.show', methods: ['GET'])]
    public function candidatesByOffer(Offer $offer, PaginatorInterface $paginator, Request $request): Response
    {
        $candidates = $paginator->paginate(
            target: $offer->getCandidates(),
            page: $request->query->getInt('page', 1),
            limit: 5
        );

        return $this->render('pages/candidate/candidates_by_offer.html.twig', ['candidates' => $candidates]);
    }

    /**
     * This controller show all candidates by company
     * @param Company $company
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/my-applicants?{company}', name: 'offer.all.candidates.show', methods: ['GET'])]
    public function candidatesByCompany(Company $company, PaginatorInterface $paginator, Request $request): Response
    {

        $candidates = $paginator->paginate(
            target: $this->candidateRepository->findCandidatesGroupByEmail($company),
            page: $request->query->getInt('page', 1),
            limit: 5
        );

        return $this->render('pages/candidate/candidates_by_company.html.twig', ['candidates' => $candidates]);
    }

    /**
     * This controller displays the details of a candidate
     * @param Candidate $candidate
     * @param Request $request
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== candidate.getOffer().getCompany().getUser()")]
    #[Route('/my-applicants/{candidate}', name: 'candidate.show', methods: ['GET'])]
    public function candidat(Candidate $candidate, Request $request): Response
    {
        return $this->render('pages/candidate/show.html.twig', [
            'page'          => (int)$request->query->get('page'),
            'count'         => (int)$request->query->get('count'),
            'redirect'      => htmlspecialchars($request->query->get('redirect')),
            'candidates'    => $this->candidateRepository->findCandidatesForOneCompany($candidate),
        ]);
    }


    /**
     * This controller delete candidat
     * @param Candidate $candidate
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return RedirectResponse
     */
    #[Security("is_granted('ROLE_USER') and user=== candidate.getOffer().getCompany().getUser()")]
    #[Route('/my-applicants/{candidate}/delete', name: 'candidate.delete', methods: ['POST'])]
    public function delete(Candidate $candidate, Request $request, UserPasswordHasherInterface $hasher): RedirectResponse
    {
        static::pagination($request);

        if (static::isPassWordValid($hasher, $request, $candidate)) {

            $this->candidateRepository->remove($candidate, true);

            $this->eventDispatcher->dispatch(new CandidateDeleteEvent($candidate));

            $this->setCount($this->getCount() - 1); //Nombres de candidats sur la page de pagination moins le candidat supprimer

            $this->addFlash(type: 'success', message: 'Le candidat à été supprimer avec succès!');

            return $this->redirectToRoute($this->getRedirect(), [
                'company' => $candidate->getOffer()->getCompany()->getId(),
                'offer' => $candidate->getOffer()->getId(),
            ]);
        }

        return $this->redirectToRoute('candidate.show', [
            'page'          => $this->getPage(),
            'count'         => $this->getCount(),
            'candidate'     => $candidate->getId(),
            'redirect'      => $this->getRedirect(),
        ]);
    }

    /**
     * This controller allows a candidate to apply for an offer
     * @param Offer $offer
     * @param Request $request
     * @return Response
     */
    #[Route('/offers/{offer}/apply', name: 'candidate.apply', methods: ['GET', 'POST'])]
    public function apply(Offer $offer, Request $request): Response
    {
        $candidate = new Candidate();
        $candidate->setOffer($offer);

        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && $candidate->getImageFile()) {

                $this->candidateRepository->save($candidate, true);

                $this->addFlash(type: 'success', message: "Votre candidature à été envoyer avec succès !");

                return $this->redirectToRoute('home.index');
            }

            $this->addFlash(type: 'warning', message: 'Veuillez bien saisir tous les champs!');
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'  => $form->createView(),
            'candidate' => $candidate
        ]);
    }
}
