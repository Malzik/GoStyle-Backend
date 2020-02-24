<?php

namespace App\Tests\Manager;

use App\Entity\Offer;
use App\Entity\User;
use App\Manager\OfferManager;
use App\Manager\UserManager;
use App\Repository\OfferRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;

class UserManagerTest extends TestCase
{
    /**
     * @var array
     */
    private $users;

    protected function setUp(): void
    {
        for($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setId($i);
            $user->setFirstName("firstname");
            $user->setLastName("lastname");
            $user->setEmail("email$i@email.test");
            $this->users[] = $user;
        }
    }

    public function testFindProfile()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->users[1]);
        $mockRouter = $this->createMock(Router::class);
        $mockOfferManager = $this->createMock(OfferManager::class);
        $mockJwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())
            ->method("getRepository")
            ->willReturn($userRepository);

        $userManager = new UserManager($mockEm, $mockOfferManager, $mockRouter, $mockJwtManager);
        $result = $userManager->findProfile($this->users[0]);

        $this->assertEquals(200, $result["status"]);
        $this->assertEquals("email1@email.test", $result["data"]["email"]);
    }

    public function testCreateUser()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $mockRouter = $this->createMock(Router::class);
        $mockOfferManager = $this->createMock(OfferManager::class);
        $mockJwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())->method("persist")->with($this->users[1]);
        $mockEm->expects($this->once())->method("flush");

        $userManager = new UserManager($mockEm, $mockOfferManager, $mockRouter, $mockJwtManager);
        $userManager->createUser($this->users[1]);
    }

    public function testFindById()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->users[1]);
        $mockRouter = $this->createMock(Router::class);
        $mockOfferManager = $this->createMock(OfferManager::class);
        $mockJwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())
            ->method("getRepository")
            ->willReturn($userRepository);

        $userManager = new UserManager($mockEm, $mockOfferManager, $mockRouter, $mockJwtManager);
        $result = $userManager->findById($this->users[0]);

        $this->assertEquals("email1@email.test", $result->getEmail());
    }

    public function testAddOfferToUser()
    {
        $offer = new Offer();
        $offer->setName("Offre 1");
        $offer->setCode("code1");
        $offer->setDescription("Description 1");
        $offer->setDeadline(new \DateTime());
        $offer->setLogo("logo.png");

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->users[1]);
        $mockRouter = $this->createMock(Router::class);
        $mockOfferManager = $this->createMock(OfferManager::class);
        $mockOfferManager->expects($this->once())->method("findByCode")->willReturn($offer);
        $mockJwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())
            ->method("getRepository")
            ->willReturn($userRepository);
        $mockEm->expects($this->once())->method("persist")->with($this->users[1]);
        $mockEm->expects($this->once())->method("flush");

        $userManager = new UserManager($mockEm, $mockOfferManager, $mockRouter, $mockJwtManager);
        $userManager->addOfferToUser($this->users[1], "code1");

        $this->assertEquals($offer, $this->users[1]->getOffers()[0]);
    }

    public function testUpdateUser()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('find')
            ->willReturn($this->users[1]);
        $mockRouter = $this->createMock(Router::class);
        $mockOfferManager = $this->createMock(OfferManager::class);
        $mockJwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $mockJwtManager->expects($this->once())->method("create")->willReturn("mynewtoken");
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())
            ->method("getRepository")
            ->willReturn($userRepository);
        $mockEm->expects($this->once())->method("flush");

        $userManager = new UserManager($mockEm, $mockOfferManager, $mockRouter, $mockJwtManager);
        $result = $userManager->updateUser($this->users[0], $this->users[2]);

        $this->assertEquals("mynewtoken", $result["token"]);
    }
    public function testUpdatePassword()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $mockRouter = $this->createMock(Router::class);
        $mockOfferManager = $this->createMock(OfferManager::class);
        $mockJwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $mockEm = $this->createMock(EntityManager::class);
        $mockEm->expects($this->once())->method("flush");

        $userManager = new UserManager($mockEm, $mockOfferManager, $mockRouter, $mockJwtManager);
       $userManager->updatePassword();
    }
}
