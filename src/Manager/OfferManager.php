<?php


namespace App\Manager;


use Doctrine\ORM\EntityManager;
use App\Entity\Offer;

class OfferManager
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findByCode($code)
    {
        return $this->entityManager->getRepository(Offer::class)->findOneBy(["code" => $code]);
    }

    public function findAll()
    {
        $offers = $this->entityManager->getRepository(Offer::class)->findAll();

        $response = [];
        foreach ($offers as $offer){
            $response[] = [
                "name" => $offer->getName(),
                "code" => $offer->getCode(),
                "description" => $offer->getDescription(),
                "logo" => $offer->getLogo(),
                "deadline" => $offer->getDeadline()->format("Y-m-d"),
                "_links" => [
                    "item" => [
                        "self" => $this->generateUrl("apioffer", ["code"=>$offer->getCode()], 0)
                    ]
                ]
            ];
        }

        return $response;
    }
}
