<?php

namespace App\Controller\User;

use App\Entity\Offer;
use App\Entity\Company;
use App\Form\OfferType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OfferController extends AbstractController
{
    /**
     * This controller display all offers by company
     * @param Company $company
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/my-offers?{company}', name: 'offer.index', methods: ['GET', 'POST'])]
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
    #[IsGranted('ROLE_USER')]
    #[Security("is_granted('ROLE_USER') and user=== company.getUser()")]
    #[Route('/my-offers/new?{company}', name: 'offer.new', methods: ['GET', 'POST'])]
    public function new(Company $company, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        $offer = new Offer();
        return static::newUpdate($offer, $request, $manager, $session, $company);
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
    #[Route('/my-offers/{offer}/update', name: 'offer.edit', methods: ['GET', 'POST'])]
    public function update(Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session): Response
    {
        return static::newUpdate($offer, $request, $manager, $session);
    }

    /**
     * This controller create or update offer
     * @param Offer $offer
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SessionInterface $session
     * @param Company|null $company
     * @return Response
     */
    private function newUpdate(Offer $offer, Request $request, EntityManagerInterface $manager, SessionInterface $session, ?Company $company = null): Response
    {
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                if ($company) $offer->setCompany($company);

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

                $manager->persist($offer);
                $manager->flush();

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
     * This controller show detail of offer
     * @param Offer $offer
     * @return Response
     */
    #[Route('/offers/{offer}', name: 'offer.show', methods: ['GET'])]
    public function show(Offer $offer): Response
    {
        return $this->render('pages/offer/show.html.twig', [
            'offer' => $offer,
        ]);
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
    #[Route('/my-offers/{offer}/delete', name: 'offer.delete', methods: ['GET'])]
    public function delete(Offer $offer = null,  Request $request, EntityManagerInterface $manager, SessionInterface $session,  UserPasswordHasherInterface $hasher, MailerService $mailerService): Response
    {
        $plainPassword = $request->cookies->get('password');
        $OffersCountPage = intval($request->query->get('count')); //Nombres d'offres sur la page courante 
        $page = intval(htmlspecialchars($session->get('page'))); //Numéro de la page courante


        if ($hasher->isPasswordValid($offer->getCompany()->getUser(), $plainPassword)) {

            if ($offer) {

                // EMAIL DE NOTIFICATION
                foreach ($offer->getCandidates() as $candidate) {
                    $mailerService->send(
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

                $manager->remove($offer);
                $manager->flush();
            }

            $OffersCountPage -= 1; //Nombres d'offres sur la page courante moins l'offre à supprimer

            $this->addFlash(
                type: $offer ? 'success' : 'warning',
                message: $offer ? 'Votre offre à été supprimer avec succès!' : 'L\'offre demander n\'existe pas'
            );
        } else {

            $this->addFlash(
                type: 'warning',
                message: 'Le mots de passe n\'est pas valide.'
            );
        }

        return $this->redirectToRoute('offer.index', [
            'company'   => intval($request->query->get('idCompany')),
            'page'      => ($OffersCountPage > 0 && $page >= 2) || $page === 1 ?  $page  : $page - 1
        ]);
    }

    // #[Route('/test', name: 'offer.test', methods: ['GET', 'POST'])]
    // public function test(Request $request,): Response
    // {

    //     return $this->render('pages/offer/test.html.twig', []);
    // }
}