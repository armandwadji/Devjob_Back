<?php

namespace App\Controller\User;

use App\Entity\Offer;
use App\Entity\Company;
use App\Event\OfferDeleteEvent;
use App\Form\OfferType;
use App\Service\MailerService;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\VarDumper\VarDumper;

#[Route('/my-offers', name: 'offer.')]
class OfferController extends AbstractController
{
    public function __construct(
        private OfferRepository $offerRepository,
        private MailerService $mailerService,
        private EventDispatcherInterface $eventDispatcher
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
        $session->set('page', isset($_GET['page']) ? intval($request->get('page')) : 1);

        $offers = $paginator->paginate(
            target: $company->getOffer(),
            page: $request->query->getInt('page', 1),
            limit: 10
        );

        return $this->render('pages/offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    /**
     * This controller create offer
     * @param Company $company
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/new?{company}', name: 'new', methods: ['GET', 'POST'])]
    public function new(Company $company, Request $request, SessionInterface $session): Response
    {
        $offer = new Offer();
        $offer->setCompany($company);
        return static::newUpdate($offer, $request, $session);
    }

    /**
     * This controller update offer
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/{offer}/update', name: 'edit', methods: ['GET', 'POST'])]
    public function update(Offer $offer, Request $request, SessionInterface $session): Response
    {
        return static::newUpdate($offer, $request, $session);
    }

    /**
     * This controller delete offer
     * @param Offer|null $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== offer.getCompany().getUser()")]
    #[Route('/{offer}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Offer $offer = null,  Request $request, SessionInterface $session,  UserPasswordHasherInterface $hasher): Response
    {
        $OffersCountPage = intval($request->query->get('count')); //Nombres d'offres sur la page courante 
        $page = intval(htmlspecialchars($session->get('page'))); //Numéro de la page courante

        if ($hasher->isPasswordValid($offer->getCompany()->getUser(), $request->request->get('_password')) && $this->isCsrfTokenValid('delete' . $offer->getId(), $request->request->get('_token'))) {

            if ($offer) {
                $this->eventDispatcher->dispatch(new OfferDeleteEvent($offer));
                // static::sendEmail($offer);
                // $this->offerRepository->remove($offer, true);
            }

            $OffersCountPage--; //Nombres d'offres sur la page courante moins l'offre à supprimer

            $this->addFlash(
                type: $offer ? 'success' : 'warning',
                message: $offer ? 'Votre offre à été supprimer avec succès!' : 'L\'offre demander n\'existe pas'
            );
        } else {

            $this->addFlash(
                type: 'warning',
                message: 'Mots de passe et ou token invalide.'
            );
        }

        return $this->redirectToRoute('offer.index', [
            'company'   => intval($request->query->get('idCompany')),
            'page'      => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1
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
        return $this->render('pages/offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }

    /**
     * This controller create or update offer
     * @param Offer $offer
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    private function newUpdate(Offer $offer, Request $request, SessionInterface $session): Response
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
                $offersTotalCount = isset($_GET['count']) ? (int) htmlspecialchars($_GET['count']) + 1 : null; //Nombres d'offres sur la page courante

                $this->addFlash(
                    type: 'success',
                    message: $offer->getId() ? "Votre offre à été éditer avec succès!" : 'Votre offre à été créer avec succès!',
                );

                $this->offerRepository->save($offer, true);

                return $this->redirectToRoute('offer.index', [
                    'company'    => $offer->getCompany()->getId(),
                    'page'  => !$offersTotalCount ?  $session->get('page') : ceil($offersTotalCount / 10)
                ]);
            }

            $this->addFlash(
                type: 'warning',
                message: 'Veuillez bien saisir tous les champs!'
            );
        }

        return $this->render('pages/offer/new_update_offer.html.twig', [
            'formOffer' => $form->createView(),
            'editMode'  => $offer->getId()
        ]);
    }

    /**
     * This method send Email of notification after delete offer
     * @param \App\Entity\Offer $offer
     * @return void
     */
    private function sendEmail(Offer $offer): void
    {
        foreach ($offer->getCandidates() as $candidate) {
            $this->mailerService->send(
                $candidate->getEmail(),
                'Réponse candidature pour le poste :' . $offer->getName(),
                'candidate_email.html.twig',
                [
                    'candidate' => $candidate,
                    'offer' => $offer,
                    'company' => $offer->getCompany(),
                    'contact' => $offer->getCompany()->getUser()
                ]
            );
        }
    }

    // #[Route('/test', name: 'offer.test', methods: ['GET', 'POST'])]
    // public function test(Request $request,): Response
    // {

    //     return $this->render('pages/offer/test.html.twig', []);
    // }
}
