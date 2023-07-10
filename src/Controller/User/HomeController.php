<?php

namespace App\Controller\User;

use App\Repository\OfferRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * This controller displays all offers
     * @param OfferRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'home.index', methods: ['GET', 'POST'])]
    public function index(OfferRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        // dd($request->request->get('search'));
        $offers = $paginator->paginate(
            target  :$repository->search($request->request->get('search')),
            page    :$request->query->getInt('page', 1),
            limit   :12,
        );

        return $this->render('pages/home.html.twig', ['offers' => $offers]);
    }
}
