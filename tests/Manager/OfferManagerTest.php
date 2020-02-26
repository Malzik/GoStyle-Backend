<?php


namespace App\Tests\Manager;


use App\Entity\Offer;
use App\Manager\OfferManager;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;

class OfferManagerTest extends TestCase
{
    /**
     * @var array
     */
    private $offers;

    protected function setUp(): void
    {
        for($i = 0; $i < 3; $i++) {
            $offer = new Offer();
            $offer->setName("Offre $i");
            $offer->setCode("code$i");
            $offer->setDescription("Description $i");
            $offer->setDeadline(new \DateTime());
            $offer->setLogo("logo.png");
            $this->offers[] = $offer;
        }
    }

    public function testGetAllOffers()
    {
        $offerRepository = $this->createMock(OfferRepository::class);
        $offerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($this->offers);
        $mockRouter = $this->createMock(Router::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())
            ->method("getRepository")
            ->willReturn($offerRepository);

        $offerManager = new OfferManager($mockEm, $mockRouter);
        $result = $offerManager->findAll();

        $this->assertEquals(sizeof($this->offers), sizeof($result));
        $this->assertEquals($this->offers[0]->getName(), $result[0]["name"]);
    }



    public function testGetOneOfferByIdReturnOffer()
    {
        $offerRepository = $this->createMock(OfferRepository::class);
        $offerRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($this->offers[2]);
        $mockRouter = $this->createMock(Router::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())
            ->method("getRepository")
            ->willReturn($offerRepository);

        $offerManager = new OfferManager($mockEm, $mockRouter);
        $result = $offerManager->findByCode("code2");

        $this->assertEquals("Offre 2", $result->getName());
    }
}
