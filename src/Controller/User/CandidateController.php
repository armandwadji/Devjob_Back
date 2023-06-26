<?php

namespace App\Controller\User;

use App\Entity\Offer;
use App\Entity\Company;
use App\Entity\Candidate;
use App\Event\CandidateDeleteEvent;
use App\Form\CandidateType;
use App\Repository\CandidateRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CandidateController extends AbstractController
{

    public function __construct(
        private CandidateRepository $candidateRepository,
        private EventDispatcherInterface $eventDispatcher
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

        return $this->render('pages/candidate/candidates_by_offer.html.twig', [
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
    #[Route('/my-applicants?{company}', name: 'offer.all.candidates.show', methods: ['GET'])]
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
    #[Security("is_granted('ROLE_USER') and user=== candidate.getOffer().getCompany().getUser()")]
    #[Route('/my-applicants/{candidate}', name: 'candidate.show', methods: ['GET'])]
    public function candidat(Candidate $candidate, Request $request): Response
    {
        return $this->render('pages/candidate/show.html.twig', [
            'candidate'             => $candidate,
            'offer'                 => intval($request->query->get('offer')),
            'candidatesForOffer'    => intval($request->query->get('candidates')),
            'page'                  => intval($request->query->get('page')),
            'isAdmin'               => false

        ]);
    }


    /**
     * This controller delete candidat
     * @param Candidate|null $candidate
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== candidate.getOffer().getCompany().getUser()")]
    #[Route('/my-applicants/{candidate}/delete', name: 'candidate.delete', methods: ['POST'])]
    public function delete(Candidate $candidate = null, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $OffersCountPage = intval($request->query->get('count')); //Nombres de candidats sur la page courante
        $pageNumber = intval($request->query->get('page')); //numéro de la page courante
        $passwordAndTokenValid = $hasher->isPasswordValid($candidate->getOffer()->getCompany()->getUser(), $request->request->get('_password')) && $this->isCsrfTokenValid('delete' . $candidate->getId(), $request->request->get('_token')); //user password and token valids

        if ($passwordAndTokenValid) {

            $this->eventDispatcher->dispatch(new CandidateDeleteEvent($candidate));
            $this->candidateRepository->remove($candidate, true);
            $OffersCountPage--; //Nombres de candidats sur la page courante moins le candidat supprimer

            $this->addFlash(type: 'success', message: 'Le candidat à été supprimer avec succès!');

            if ($OffersCountPage === 0 && $pageNumber === 1) return $this->redirectToRoute('offer.index', ['company' => $candidate->getOffer()->getCompany()->getId()]);
        } else {

            $this->addFlash(type: 'warning', message: 'Mots de passe et ou token invalide.');

            return $this->redirectToRoute('candidate.show', [
                'candidate'     => $candidate->getId(),
                'offer'         => intval($request->query->get('idOffer')),
                'candidates'    => $OffersCountPage,
                'page'          => $pageNumber,
            ]);
        }

        return $this->redirectToRoute('offer.candidates.show', [
            'offer'     => intval($request->query->get('idOffer')),
            'page'      => (($OffersCountPage > 0 && $pageNumber >= 2) || $pageNumber === 1 || $pageNumber === 0) ?  $pageNumber  : $pageNumber - 1
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
        $form = $this->createForm(CandidateType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid() && $candidate->getImageFile()) {

                $candidate->setOffer($offer);
                $this->candidateRepository->save($candidate, true);
                
                $this->addFlash(type: 'success', message: "Votre candidature à été envoyer avec succès !");

                return $this->redirectToRoute('home.index');
            }

            $this->addFlash(type: 'warning', message: 'Veuillez bien saisir tous les champs!');
        }

        return $this->render('pages/candidate/apply.html.twig', [
            'form'  => $form->createView(),
            'offer' => $offer
        ]);
    }
}
