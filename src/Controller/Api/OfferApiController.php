<?php

namespace App\Controller\Api;

use App\Model\OfferModel;
use App\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api.')]
class OfferApiController extends AbstractController
{
    private const HEADER = [
        'Access-Control-Allow-Origin' => '*',
        'Content-Type' => 'application/json'
    ];

    public function __construct(
        private readonly OfferRepository $offerRepository,
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
        $offset = (int)$request->query->get('offset');
        $limit = (int)$request->get('limit') ?: 12;

        $offers = $this->offerRepository->offersApi(offset: $offset, limit: $limit);
        
        foreach ($offers as $offer) {
            $offer->setBaseUrl($request->server->get('BASE_URL'));
        }
        
        return $this->json(
            data: [
                'jobs'  => OfferModel::fromOfferEntities($offers),
                'total' => count($this->offerRepository->findAll()),
            ],
            status: 200,
            headers: static::HEADER,
            context: ['groups' => 'offer:list']
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
        $id = (int)$request->get('id', 0);
        $offer = $this->offerRepository->find(['id' => $id]);

        if (!$offer) {
            return $this->json(['error' => 'job not found'], 400);
        }

        $offer->setBaseUrl($request->server->get('BASE_URL'));

        return $this->json(
            data: OfferModel::fromOfferEntity($offer),
            status: 200,
            headers: static::HEADER,
            context: ['groups' => 'offer:detail']
        );
    }

    /**
     * This controller returns offers based on search parameters
     * @param Request $request
     * @return Response
     */
    #[Route('/jobs/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $offset = (int)$request->query->get('offset');
        $limit = (int)$request->query->get('limit') ?: 12;
        $location = (string)$request->query->get('location', null);

        $fulltime = $request->query->get('fulltime') ?: 0;
        if ($fulltime !== 0 && $fulltime !== '1') return $this->json(['error' => 'fulltime must take the values 0 or 1'], 400);

        $text = (string)$request->query->get('text', null);

        $offers = $this->offerRepository->offersApi(offset: $offset, limit: $limit, location: $location, fulltime: $fulltime, text: $text);

        foreach ($offers as $offer) {
            $offer->setBaseUrl($request->server->get('BASE_URL'));
        }


        return $this->json(
            data: [
                'jobs' => OfferModel::fromOfferEntities($offers),
                'total' => count($this->offerRepository->offersApi(offset: 0, limit: 100000, location: $location, fulltime: $fulltime, text: $text))
            ],
            status: 200,
            headers: static::HEADER,
            context: ['groups' => 'offer:list']
        );
    }
}
