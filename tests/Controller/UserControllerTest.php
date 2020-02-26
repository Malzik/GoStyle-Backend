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
use App\EventListener\JWTListener;
use App\Manager\OfferManager;
use App\Manager\UserManager;
use Exception;
use JMS\Serializer\SerializerInterface;
use Lcobucci\JWT\Token;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
            ->method('findProfile')
            ->willReturn(["data" => $this->users[0], "status" => 200]);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode($this->users[0]));
        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->profile();

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals(json_encode($this->users[0]), $result->getContent());
    }

    public function testProfileThrowException()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('findProfile')
            ->willThrowException(new Exception("fake exception", 500));
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode($this->users[0]));
        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->profile();

        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testUpdateProfile()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('updateUser')
            ->willReturn("token");
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->any())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $serializer->expects($this->any())
            ->method("serialize")
            ->willReturn(json_encode($this->users[0]));
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $request = new Request();

        $mockJwt = $this->createMock(TokenStorage::class);
        $test = new Container();
        $test->set("security.token_storage", $mockJwt);

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($test);
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
            ->willThrowException(new Exception("fakeexception", 500));
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method("deserialize")
            ->willReturn($this->users[0]);
        $serializer->expects($this->once())
            ->method("serialize")
            ->willReturn(json_encode($this->users[0]));
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $request = new Request();

        $mockJwt = $this->createMock(TokenStorage::class);
        $test = new Container();
        $test->set("security.token_storage", $mockJwt);

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($test);
        $result = $userController->updateProfil($request, $validatorMock);

        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testUpdatePassword()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('updatePassword');
        $userManager->expects($this->once())
            ->method('findById')
            ->willReturn($this->users[0]);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $userPasswordeEncodeMock->expects($this->once())
            ->method("encodePassword")
            ->willReturn("encodedpassword");

        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->expects($this->once())->method("getContent")->willReturn(json_encode(["new_password" => "password"]));

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->updatePassword($mockRequest, $validatorMock, $userPasswordeEncodeMock);

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
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn(["password" => "bad password"]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->expects($this->once())->method("getContent")->willReturn(json_encode(["new_password" => "password"]));

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->updatePassword($mockRequest, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testUpdatePasswordThrowException()
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('updatePassword')
            ->willThrowException(new Exception("fake exception", 500));
        $userManager->expects($this->once())
            ->method('findById')
            ->willReturn($this->users[0]);
        $offerManager = $this->createMock(OfferManager::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method("validate")
            ->willReturn([]);
        $userPasswordeEncodeMock = $this->createMock(UserPasswordEncoderInterface::class);
        $userPasswordeEncodeMock->expects($this->once())
            ->method("encodePassword")
            ->willReturn("encodedpassword");

        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->expects($this->once())->method("getContent")->willReturn(json_encode(["new_password" => "password"]));

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->updatePassword($mockRequest, $validatorMock, $userPasswordeEncodeMock);

        $this->assertEquals(500, $result->getStatusCode());
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

        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->expects($this->once())->method("getContent")->willReturn(json_encode(["code" => "fakecode"]));

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->addOfferToUser($mockRequest);

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

        $mockJwt = $this->createMock(TokenStorage::class);
        $container = new Container();
        $container->set("security.token_storage", $mockJwt);
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->expects($this->once())->method("getContent")->willReturn(json_encode(["code" => "fakecode"]));

        $userController = new UserController($userManager, $offerManager, $serializer);
        $userController->setContainer($container);
        $result = $userController->addOfferToUser($mockRequest);

        $this->assertEquals(500, $result->getStatusCode());
    }
}
