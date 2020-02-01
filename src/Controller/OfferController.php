<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OfferController
 * @Route("/api")
 * @package App\Controller
 */
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
        $offer = $this->offerRepository->find($id);
        if(empty($offer))
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        return new JsonResponse($this->offerRepository->find($id));
    }
}
