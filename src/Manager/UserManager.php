<?php


namespace App\Manager;


use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var JWTTokenManagerInterface
     */
    private $JWTTokenManager;
    /**
     * @var OfferManager
     */
    private $offerManager;

    public function __construct(EntityManagerInterface $entityManager, OfferManager $offerManager, RouterInterface $router, JWTTokenManagerInterface $JWTTokenManager)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->JWTTokenManager = $JWTTokenManager;
        $this->offerManager = $offerManager;
    }

    public function findProfile(?UserInterface $user)
    {
        $profile = $this->entityManager->getRepository(User::class)->find($user->getId());

        $offers = array();
        foreach ($profile->getOffers() as $offer){
            $offers[] = [
                "name" => $offer->getName(),
                "code" => $offer->getCode(),
                "description" => $offer->getDescription(),
                "logo" => $offer->getLogo(),
                "deadline" => $offer->getDeadline()->format("Y-m-d"),
                "_links" => [
                    "item" => [
                        "self" => $this->router->generate("apioffer", ["code"=>$offer->getCode()], 0)
                    ]
                ]
            ];
        }
        $response[] = [
            "last_name" => $profile->getLastName(),
            "first_name" => $profile->getFirstName(),
            "email" => $profile->getEmail(),
            "offers" => $offers
        ];
        return ["data" => current($response), "status" => Response::HTTP_OK];
    }

    public function updateUser(?UserInterface $user, UserInterface $updatedUser)
    {
        $currentUser = $this->entityManager->getRepository(User::class)->find($user->getId());

        $currentUser->setFirstName($updatedUser->getFirstName());
        $currentUser->setLastName($updatedUser->getLastName());
        $currentUser->setEmail($updatedUser->getEmail());

        $this->entityManager->flush();

        $token = $this->JWTTokenManager->create($currentUser);

        return ["token" => $token];
    }

    public function findById(?UserInterface $getUser)
    {
        return $this->entityManager->getRepository(User::class)->find($getUser->getId());
    }

    public function addOfferToUser(?UserInterface $user, $code)
    {
        $currentUser = $this->findById($user);
        $offer = $this->offerManager->findByCode($code);

        $currentUser->addOffer($offer);
        $this->entityManager->persist($currentUser);
        $this->entityManager->flush();
    }

    public function createUser(User $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function updatePassword()
    {
        $this->entityManager->flush();
    }
}
