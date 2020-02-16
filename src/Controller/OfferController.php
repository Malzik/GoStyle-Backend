<?php

namespace App\Controller;

use App\Manager\OfferManager;
use App\Repository\OfferRepository;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
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
     * @var OfferManager
     */
    private $offerManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * OfferController constructor.
     * @param OfferManager $offerManager
     * @param SerializerInterface $serializer
     */
    public function __construct(OfferManager $offerManager, SerializerInterface $serializer)
    {
        $this->offerManager = $offerManager;
        $this->serializer = $serializer;
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
     * @Security(name="Bearer")
     **/
    public function offers()
    {
        try {
            $offers = $this->offerManager->findAll();
            return $this->getJsonResponse($offers);
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/offers/{code}", name="offer", methods={"GET"})
     * @param string code
     * @return JsonResponse
     * @SWG\Response(
     *     response=200,
     *     description="Returns offer by code",
     *     @Model(type=Offer::class)
     * )
     * @SWG\Tag(name="Offers")
     * @Security(name="Bearer")
     */
    public function offerByCode(string $code)
    {
        try {
            $offer = $this->offerManager->findByCode($code);
            if(empty($offer))
                return $this->getJsonResponse(null, Response::HTTP_NOT_FOUND);
            return $this->getJsonResponse($offer);
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    private function getJsonResponse($data = null, int $status = 200, $headers = []) : JsonResponse
    {
        $serializedData = $data;
        if (!is_null($data)){
            $serializedData = $this->serializer->serialize($data, 'json');
        }
        $response = new JsonResponse(null, $status, $headers);
        $response->setContent($serializedData);

        return $response;
    }
}
