<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

/**
 * Class LoginController
 * @Route("/api")
 * @package App\Controller
 */
class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     * * @SWG\Response(
     *     response=200,
     *     description="Login",
     * )
     * @SWG\Parameter(
     *     name="User",
     *     in="body",
     *     description="The field used to login",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Tag(name="Users")
     */
    public function login() {
    }
}
