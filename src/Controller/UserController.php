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
     * @Route("/profil", name="profil", methods={"GET"})
     */
    public function profil(Request $request)
    {
        return new JsonResponse($this->userRepository->find($this->getUser()->getId()));
    }

    /**
     * @Route("/profil", name="update.profil", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfil(Request $request)
    {
        $data = json_decode($request->getContent(),true);
        $user = $this->userRepository->find($this->getUser()->getId());

        if(empty($user))
            return new JsonResponse("internal error", Response::HTTP_INTERNAL_SERVER_ERROR);
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data, false);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        dd($form);
        return new JsonResponse($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/register", name="create.user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function createUser(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(),true);
        $form->submit($data);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse(Response::HTTP_CREATED);
        }

        return new JsonResponse(Response::HTTP_BAD_REQUEST);

    }
}
