<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Offer;

/**
 * Class OfferController
 * @package App\Controller
 * @Route("/api", name="api")
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
     * @SWG\Response(
     *     response=200,
     *     description="Returns all offers",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Offer::class))
     *     )
     * )
     * @SWG\Tag(name="Offers")
     **/
    public function offers()
    {
        $offers = $this->offerRepository->findAll();
        foreach ($offers as $offer){
            $response[] = [
                "name" => $offer->getName(),
                "code" => $offer->getCode(),
                "description" => $offer->getDescription(),
                "logo" => $offer->getLogo(),
                "deadline" => $offer->getDeadline()->format("Y-m-d"),
                "_links" => [
                    "item" => [
                        "self" => $this->generateUrl("apioffer", ["id"=>$offer->getId()], 0)
                    ]
                ]
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/offers/{id}", name="offer", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     * @SWG\Response(
     *     response=200,
     *     description="Returns offer by id",
     *     @Model(type=Offer::class)
     * )
     * @SWG\Tag(name="Offers")
     */
    public function offerById(int $id)
    {
        $offer = $this->offerRepository->find($id);
        if(empty($offer))
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        return new JsonResponse($this->offerRepository->find($id));
    }
}
