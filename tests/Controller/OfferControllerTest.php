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
use App\Manager\OfferManager;
use DateTime;
use Exception;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;

class OfferControllerTest extends TestCase
{
    /**
     * @var array
     */
    private $offers;

    protected function setUp(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $offer = new Offer();
            $offer->setName("Offre $i");
            $offer->setCode("code$i");
            $offer->setDescription("Description $i");
            $offer->setDeadline(new DateTime());
            $offer->setLogo("logo.png");
            $this->offers[] = $offer;
        }
    }

    public function testOfferByCode()
    {
        $offerManager = $this->createMock(OfferManager::class);
        $offerManager->expects($this->once())
            ->method('findByCode')
            ->willReturn($this->offers[1]);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode($this->offers[1]));


        $offerController = new OfferController($offerManager, $serializer);
        $result = $offerController->offerByCode("code");

        $offer = json_decode($result->getContent(), true);
        $this->assertEquals($this->offers[1]->getName(), $offer["name"]);
    }

    public function testOfferByCodeWithCodeNotFound()
    {
        $offerManager = $this->createMock(OfferManager::class);
        $offerManager->expects($this->once())
            ->method('findByCode')
            ->willReturn([]);
        $serializer = $this->createMock(SerializerInterface::class);

        $offerController = new OfferController($offerManager, $serializer);
        $result = $offerController->offerByCode("fakecode");

        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testOfferByCodeThrowException()
    {
        $offerManager = $this->createMock(OfferManager::class);
        $offerManager->expects($this->once())
            ->method('findByCode')
            ->willThrowException(new Exception("fake error", 500));
        $serializer = $this->createMock(SerializerInterface::class);


        $offerController = new OfferController($offerManager, $serializer);
        $result = $offerController->offerByCode("code");

        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testOffers()
    {
        $offerManager = $this->createMock(OfferManager::class);
        $offerManager->expects($this->once())
            ->method('findAll')
            ->willReturn($this->offers);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode($this->offers));


        $offerController = new OfferController($offerManager, $serializer);
        $result = $offerController->offers();
        $offers = json_decode($result->getContent(), true);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(sizeof($this->offers), sizeof($offers));
    }

    public function testOffersThrowException()
    {
        $offerManager = $this->createMock(OfferManager::class);
        $offerManager->expects($this->once())
            ->method('findAll')
            ->willThrowException(new Exception("fake exception", 500));
        $serializer = $this->createMock(SerializerInterface::class);

        $offerController = new OfferController($offerManager, $serializer);
        $result = $offerController->offers();

        $this->assertEquals(500, $result->getStatusCode());
    }
}
