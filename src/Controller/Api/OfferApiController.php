<?php

namespace App\Controller\Api;

use App\Entity\Offer;
use App\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api.')]
class OfferApiController extends AbstractController
{
    const HEADER = ['Access-Control-Allow-Origin' => '*', 'Content-Type' => 'application/json'];

    public function __construct(
        private OfferRepository $offerRepository,
    ) {
    }

    /**
     * This controller returns offers
     * @param Request $request
     * @return Response
     */
    #[Route('/jobs', name: 'offers', methods: ['GET', 'POST'])]
    public function offers(Request $request): Response
    {

        $offset = intval($request->query->get('offset'));
        $limit = intval($request->get('limit')) ?: 12;
        return $this->json(
            data    : [
                        'jobs'  => static::offersFormat($this->offerRepository->offersApi(offset: $offset, limit: $limit), $request),
                        'total' => count($this->offerRepository->findAll()),
                      ],
            status  : 200,
            headers : static::HEADER,
            context : ['groups' => 'offer:read']
        );
    }

    /**
     * This controller returns an offer based on its ID
     * @param Request $request
     * @return Response
     */
    #[Route('/job/{id}', name: 'offer', methods: ['GET'])]
    public function offer(Request $request): Response
    {
        $id = intval($request->get('id', 0));
        $offer = $this->offerRepository->find(['id' => $id]);

        if (!$offer) {
            return $this->json(['error' => 'job not found'], 400);
        }

        return $this->json(
            data    :['jobs' => static::offerFormat($offer, $request)], 
            status  :200, 
            headers :static::HEADER, 
            context :['groups' => 'offer:detail']);
    }

    /**
     * This controller returns offers based on search parameters
     * @param Request $request
     * @return Response
     */
    #[Route('/jobs/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $offset = intval($request->query->get('offset'));
        $limit = intval($request->query->get('limit')) ?: 12;
        $location = strval($request->query->get('location', null));
        $fulltime = boolval($request->query->get('fulltime', null));
        $text = strval($request->query->get('text', null));

        return $this->json(
            data    :[
                        'jobs' => static::offersFormat(
                            $this->offerRepository->offersApi(
                                offset: $offset,
                                limit: $limit,
                                location: $location,
                                fulltime: $fulltime,
                                text: $text
                            ),
                            $request
                        ),
                        'total' => count($this->offerRepository->findAll())
                    ],
            status  :200,
            headers :[],
            context :['groups' => 'offer:read']
        );
    }

    /**
     * This method return offers with the correct format
     * @param array $offers
     * @return array
     */
    private function offersFormat(array $offers, Request $request): array
    {
        // https://devjobs.wadji.cefim.o2switch.site/files/candidates/cvjennyterrible-6499606e11b9b405973973.pdf
        $offersFormat = [];
        foreach ($offers as $offer) {
            $offerFormat = [
                'company'           => $offer->getCompany()->getName(),
                'contract'          => $offer->getContract()->getName(),
                'id'                => $offer->getId(),
                'location'          => $offer->getCompany()->getCountry(),
                'logo'              => $offer->getCompany()->getImageName() ? $request->server->get('BASE_URL') . '/images/company/' . $offer->getCompany()->getImageName() : 'https://picsum.photos/id/' . $offer->getId() . '/250/250',
                'logoBackground'    => $offer->getCompany()->getColor(),
                'position'          => $offer->getName(),
                'postedAt'          => $offer->getCreatedAt()->getTimestamp(),
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
    private function offerFormat(Offer $offer, Request $request): array
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
            'apply'             => $request->server->get('BASE_URL') . '/' . 'offers/' . $offer->getId() . '/apply',
            'company'           => $offer->getCompany()->getName(),
            'contract'          => $offer->getContract()->getName(),
            'description'       => $offer->getDescription(),
            'id'                => $offer->getId(),
            'location'          => $offer->getCompany()->getCountry(),
            'logo'              => $offer->getCompany()->getImageName() ? $request->server->get('BASE_URL') . '/images/company/' . $offer->getCompany()->getImageName() : 'https://picsum.photos/id/' . $offer->getId() . '/250/250',
            'logoBackground'    => $offer->getCompany()->getColor(),
            'position'          => $offer->getName(),
            'postedAt'          => $offer->getCreatedAt()->getTimestamp(),
            'requirements'      => ['content' => $offer->getRequirement()->getContent(), 'items' => $requirementItems,],
            'roles'             => ['content' => $offer->getRole()->getContent(), 'items' => $roleItems,],
            'website'           => $offer->getUrl(),

        ];

        return $offerFormat;
    }
}
