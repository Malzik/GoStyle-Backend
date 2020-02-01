<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @Route("/api")
 * @package App\Controller
 */
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
     * @Route("/user", name="user", methods={"GET"})
     */
    public function profil(Request $request)
    {
        return new JsonResponse($this->userRepository->find($this->getUser()->getId()));
    }

    /**
     * @Route("/user", name="update.user", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfil(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $user = $this->userRepository->find($this->getUser()->getId());

        if(empty($user))
            return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);

        $form = $this->createForm(UserType::class, $user);
        $form->submit($data, false);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
