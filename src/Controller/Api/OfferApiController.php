<?php

namespace App\Controller\Api;

use App\Entity\Offer;
use App\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferApiController extends AbstractController
{
    /**
     * This controller returns offers
     * @param OfferRepository $offerRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/api/jobs', name: 'api.offers', methods: ['GET', 'POST'])]
    public function offers(OfferRepository $offerRepository, Request $request): Response
    {

        $offset = intval($request->query->get('offset'));
        $limit = intval($request->get('limit')) ?: 12;
        return $this->json(
            [
                'jobs' => static::offersFormat($offerRepository->offersApi(
                    offset: $offset,
                    limit: $limit
                )),
                'total' => count($offerRepository->findAll())
            ],
            200,
            [],
            ['groups' => 'offer:read']
        );
    }

    #[Route('/api/job/{id}', name: 'api.offer', methods: ['GET'])]
    /**
     * This controller returns an offer based on its ID
     * @param OfferRepository $offerRepository
     * @param Request $request
     * @return Response
     */
    public function offer(OfferRepository $offerRepository, Request $request): Response
    {
        $id = intval($request->get('id', 0));
        $offer = $offerRepository->find(['id' => $id]);

        if (!$offer) {
            return $this->json(['error' => 'job not found'], 400);
        }

        return $this->json(['jobs' => static::offerFormat($offer)], 200, [], ['groups' => 'offer:detail']);
    }

    /**
     * This controller returns offers based on search parameters
     * @param OfferRepository $offerRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/api/jobs/search', name: 'api.search', methods: ['GET'])]
    public function search(OfferRepository $offerRepository, Request $request): Response
    {
        $offset = intval($request->query->get('offset'));
        $limit = intval($request->query->get('limit')) ?: 12;
        $location = $request->query->get('location', null);
        $fulltime = boolval($request->query->get('fulltime', null));
        $text = strval($request->query->get('text', null));
        return $this->json(
            [
                'jobs' => static::offersFormat($offerRepository->offersApi(
                    offset: $offset,
                    limit: $limit,
                    location: $location,
                    fulltime: $fulltime,
                    text: $text
                )),
                'total' => count($offerRepository->findAll())
            ],
            200,
            [],
            ['groups' => 'offer:read']
        );
    }

    /**
     * This method return offers with the correct format
     * @param array $offers
     * @return array
     */
    private function offersFormat(array $offers): array
    {
        $offersFormat = [];
        foreach ($offers as $offer) {
            $offerFormat = [
                'company' => $offer->getCompany()->getName(),
                'contract' => $offer->getContract()->getName(),
                'id' => $offer->getId(),
                'location' => $offer->getCompany()->getCountry(),
                'logo' => $offer->getCompany()->getImageName(),
                'logoBackground' => $offer->getCompany()->getColor(),
                'position' => $offer->getName(),
                'postedAt' =>  $offer->getCreatedAt()->getTimestamp(),
            ];

            $offersFormat[] = $offerFormat;
        }

        return $offersFormat;
    }

    /**
     * This method convert offer with the correct format
     * @param Offer $offer
     * @return array
     */
    private function offerFormat(Offer $offer): array
    {
        // REQUIREMENTS ITEMS
        $requirementItems = [];
        foreach ($offer->getRequirement()->getRequirementItems() as $item) {
            $requirementItems[] = $item->getName();
        }

        // ROLES ITEMS
        $roleItems = [];
        foreach ($offer->getRole()->getRoleItems() as $item) {
            $roleItems[] = $item->getName();
        }

        $offerFormat = [
            'apply' => 'http://127.0.0.1:8000/offers/'. $offer->getId() .'/apply?url='. $offer->getUrl(),
            'company' => $offer->getCompany()->getName(),
            'contract' => $offer->getContract()->getName(),
            'description' => $offer->getDescription(),
            'id' => $offer->getId(),
            'location' => $offer->getCompany()->getCountry(),
            'logo' => $offer->getCompany()->getImageName(),
            'logoBackground' => $offer->getCompany()->getColor(),
            'position' => $offer->getName(),
            'postedAt' => $offer->getCreatedAt()->getTimestamp(),
            'requirements' => [
                'content' => $offer->getRequirement()->getContent(),
                'items' => $requirementItems,
            ],
            'roles' => [
                'content' => $offer->getRole()->getContent(),
                'items' => $roleItems,
            ],
            'website' => $offer->getUrl(),

        ];

        return $offerFormat;
    }
}
