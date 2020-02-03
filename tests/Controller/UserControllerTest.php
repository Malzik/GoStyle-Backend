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
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends TestCase
{

    public function testProfil()
    {
        $user = new User();
        $user->setFirstName("firstname");
        $user->setLastName("lastname");
        $user->setEmail("email");
        $user->setPassword("password");


        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->expects($this->any())
            ->method('find')
            ->willReturn($user);

        $userController = new UserController($userRepository);
        $profil = $userController->profil();
        $profil = json_decode($profil->getContent(), true);
        $this->assertEquals($user->getEmail(), $profil["email"]);
    }

    public function testProfilWithUndefinedUser()
    {
        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->expects($this->any())
            ->method('find')
            ->willReturn([]);

        $userController = new UserController($userRepository);
        $profil = $userController->profil();
        $profil = json_decode($profil->getStatusCode(), true);
        $this->assertEquals(404, $profil);
    }

    public function testCreateUser()
    {
        $request = $this->createMock(Request::class);

        $request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn("{\"first_name\": \"firstName\",\"last_name\": \"lastName\",\"email\": \"email\",\"password\": \"password\"}");
        $userRepository = $this->createMock(UserRepository::class);

        $userController = new UserController($userRepository);
        $createUser = $userController->createUser($request);
        $createUser = json_decode($createUser->getContent(), true);
        $this->assertEquals(404, $createUser);
    }

    public function testUpdateProfil()
    {

    }
}
