<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home.index', methods: ['GET'])]
    public function index(OfferRepository $repository, PaginatorInterface $paginator, Request $request ): Response
    {
        $offers = $paginator->paginate(
            $repository->findOfferOrderDesc(),
            $request->query->getInt('page', 1),
            12,
        );

        return $this->render('pages/home.html.twig', [
            'offers' => $offers,
        ]);
    }
}
