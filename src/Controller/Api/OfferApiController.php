<?php

namespace App\Controller\Api;

use App\Repository\OfferRepository;
use Symfony\Component\Intl\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfferApiController extends AbstractController
{
    #[Route('/api/jobs', name: 'api.offers', methods: ['GET', 'POST'])]
    public function offers(OfferRepository $offerRepository, Request $request): Response
    {

        $offset = intval($request->query->get('offset'));
        $limit = intval($request->get('limit')) ?: 12;
        return $this->json(['jobs' => static::offersFormat($offerRepository->offersOffsetLimit($offset, $limit)), 'total' =>count($offerRepository->findAll())  ], 200, [], ['groups' => 'offer:read']);
    }

    #[Route('/api/job/{id}', name: 'api.offer', methods: ['GET'])]
    public function offer(OfferRepository $offerRepository, Request $request): Response
    {
        $id = intval($request->get('id', 0));
        $offer = $offerRepository->find(['id' => $id]);
        
        if (!$offer) {
            return $this->json(['error' => 'job not found'], 400);
        }
        
        return $this->json(['jobs' => static::offerFormat($offer)], 200, [], ['groups' => 'offer:detail']);
    }
    
    
    #[Route('/api/jobs/search', name: 'api.offer', methods: ['GET'])]
    public function search(OfferRepository $offerRepository, Request $request): Response
    {
        $offset = intval($request->query->get('offset'));
        $limit = intval($request->query->get('limit')) ?: 12;
        $location = $request->query->get('location', null);
        $fulltime = boolval($request->query->get('fulltime', null)) ;
        $text = strval($request->query->get('text', null)) ;
        return $this->json(['jobs' => static::offersFormat($offerRepository->offersOffsetLimit($offset, $limit, $location, $fulltime, $text)), 'total' =>count($offerRepository->findAll()) ], 200, [], ['groups' => 'offer:read']);
    }

    private function offersFormat($offers): array
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
                'postedAt' => $offer->getCreatedAt(),
            ];

            $offersFormat[] = $offerFormat;
        }

        return $offersFormat;
    }

    private function offerFormat($offer): array
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
            'apply' => $offer->getUrl(),
            'company' => $offer->getCompany()->getName(),
            'contract' => $offer->getContract()->getName(),
            'description' => $offer->getDescription(),
            'id' => $offer->getId(),
            'location' => $offer->getCompany()->getCountry(),
            'logo' => $offer->getCompany()->getImageName(),
            'logoBackground' => $offer->getCompany()->getColor(),
            'position' => $offer->getName(),
            'postedAt' => $offer->getCreatedAt(),
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
