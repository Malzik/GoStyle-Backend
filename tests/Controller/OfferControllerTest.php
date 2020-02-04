<?php
/**
 * Created by PhpStorm.
 * User: Alexis.H
 * Date: 31/01/2020
 * Time: 10:32
 */

namespace App\Tests\Controller;

use App\Controller\OfferController;
use App\Entity\Offer;
use App\Repository\OfferRepository;
use PHPUnit\Framework\TestCase;

class OfferControllerTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }


    public function testOfferById()
    {
        $offer = new Offer();
        $offer->setName("Offre 1");
        $offer->setCode("Code");
        $offer->setDescription("Description 1");
        $offer->setDeadline(new \DateTime());
        $offer->setLogo("logo.png");

        $offerRepository = $this->createMock(OfferRepository::class);

        $offerRepository->expects($this->any())
            ->method('find')
            ->willReturn($offer);

        $offerController = new OfferController($offerRepository);
        $offers = $offerController->offerById(1);
        $offers = json_decode($offers->getContent(), true);
        $this->assertEquals($offer->getName(), $offers["name"]);
    }

    public function testOfferByIdWithIdNotFound()
    {
        $offer = new Offer();
        $offer->setName("Offre 1");
        $offer->setCode("Code");
        $offer->setDescription("Description 1");
        $offer->setDeadline(new \DateTime());
        $offer->setLogo("logo.png");

        $offerRepository = $this->createMock(OfferRepository::class);

        $offerRepository->expects($this->any())
            ->method('find')
            ->willReturn([]);

        $offerController = new OfferController($offerRepository);
        $offers = $offerController->offerById(1);
        $offers = json_decode($offers->getStatusCode(), true);
        $this->assertEquals(404, $offers);
    }

    public function testOffers()
    {
        $offer1 = new Offer();
        $offer1->setName("Offre 1");
        $offer1->setCode("Code");
        $offer1->setDescription("Description 1");
        $offer1->setDeadline(new \DateTime());
        $offer1->setLogo("logo.png");

        $offer2 = new Offer();
        $offer2->setName("Offre 2");
        $offer2->setCode("Code");
        $offer2->setDescription("Description 2");
        $offer2->setDeadline(new \DateTime());
        $offer2->setLogo("logo.png");

        $offerRepository = $this->createMock(OfferRepository::class);

        $offerRepository->expects($this->any())
            ->method('findAll')
            ->willReturn([$offer1, $offer2]);

        $offerController = new OfferController($offerRepository);
        $offers = $offerController->offers();
        $offers = json_decode($offers->getContent(), true);
        $this->assertEquals($offer2->getName(), $offers[1]["name"]);
    }
}
