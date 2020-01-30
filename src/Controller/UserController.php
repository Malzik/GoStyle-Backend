<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/profil", name="profil", methods={"GET"})
     */
    public function profil()
    {
        return new JsonResponse($this->userRepository->find(1));
    }

    /**
     * @Route("/profil", name="update.profil", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfil(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $user = $this->userRepository->find(1);

        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        return new JsonResponse(Response::HTTP_NO_CONTENT);
    }
}
