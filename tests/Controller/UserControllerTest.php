<?php
/**
 * Created by PhpStorm.
 * User: Alexis.H
 * Date: 31/01/2020
 * Time: 12:00
 */

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Manager\OfferManager;
use App\Manager\UserManager;
use Exception;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserControllerTest extends TestCase
{
    /**
     * @var array
     */
    private $users;

    protected function setUp(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setId($i);
            $user->setFirstName("firstname");
            $user->setLastName("lastname");
            $user->setEmail("email$i@email.test");
            $user->setPassword("password");
            $this->users[] = $user;
        }
    }

    public function testProfile()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('findProfile');
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->profile();

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testProfileThrowException()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('findProfile')
            ->willThrowException(new Exception("fake exception", 500));
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $userController = new UserController($userManager, $offerManager, $serializer);
        $profile = $userController->profile();
        $this->assertEquals(500, $profile->getStatusCode());
    }

    public function testUpdateProfile()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('updateUser');
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $request = new Request();

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->updateProfil($request, $validatorMock);

        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testUpdateProfileWithInvalidatedUser()
    {
        $userManager = $this->createMock(UserManager::class);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode(["name" => "bad name"]));
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(["name" => "bad name"]);
        $request = new Request();

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->updateProfil($request, $validatorMock);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals(["name" => "bad name"], json_decode($result->getContent(), true));
    }

    public function testUpdateProfileThrowException()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('updateUser')
            ->willThrowException(new Exception("fake exception", 500));
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $request = new Request();

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->updateProfil($request, $validatorMock);

        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testUpdatePassword()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('findById')
            ->willReturn($this->users[0]);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $userPasswordeEncodeMock->expects($this->once())
            ->method("encodePassword")
            ->willReturn("encodedpassword");
        $request = new Request();
        $request->request->set("new_password", "new_password");

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->updatePassword($request, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testUpdatePasswordWithInvalidatedPassword()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('findById')
            ->willReturn($this->users[0]);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode(["password" => "bad password"]));
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(["password" => "bad password"]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $userPasswordeEncodeMock->expects($this->once())
            ->method("encodePassword")
            ->willReturn("encodedpassword");
        $request = new Request();
        $request->request->set("new_password", "new_password");

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->updatePassword($request, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testCreateUser()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('createUser');
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $userPasswordeEncodeMock->expects($this->once())
            ->method("encodePassword")
            ->willReturn("encodedpassword");
        $request = new Request();

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->createUser($request, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testCreateUserWithInvalidatedUser()
    {
        $userManager = $this->createMock(UserManager::class);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $serializer->expects($this->any())
            ->method("serialize")
            ->willReturn(json_encode(["name" => "fakeerror"]));
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(["name" => "fakeerror"]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $request = new Request();

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->createUser($request, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals(["name" => "fakeerror"], json_decode($result->getContent(), true));
    }

    public function testCreateUserThrowException()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('createUser')
            ->willThrowException(new Exception("fake exception", 500));
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $userPasswordeEncodeMock->expects($this->once())
            ->method("encodePassword")
            ->willReturn("encodedpassword");
        $request = new Request();

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->createUser($request, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testAddOfferToUser()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('addOfferToUser');
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $request = new Request();
        $request->request->set("code", "code1");

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->addOfferToUser($request);

        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testAddOfferToUserThrowException()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('addOfferToUser')
            ->willThrowException(new Exception("fake exception", 500));
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $request = new Request();
        $request->request->set("code", "code1");

        $userController = new UserController($userManager, $offerManager, $serializer);
        $result = $userController->addOfferToUser($request);

        $this->assertEquals(500, $result->getStatusCode());
    }
}
