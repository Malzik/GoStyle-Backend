<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends AbstractController
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * OfferController constructor.
     * @param OfferRepository $offerRepository
     */
    public function __construct(OfferRepository $offerRepository)
    {
        $this->offerRepository = $offerRepository;
    }

    /**
     * @Route("/offers", name="offers", methods={"GET"})
     */
    public function offers()
    {
        $offers = $this->offerRepository->findAll();
        return new JsonResponse($offers);
    }

    /**
     * @Route("/offers/{id}", name="offer", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     */
    public function offerById(int $id)
    {
        return new JsonResponse($this->offerRepository->find($id));
    }
}
