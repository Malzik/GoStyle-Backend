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
        $ucmId = $this->container->get('security.token_storage')->getToken()->getUser()->getId();
        return new JsonResponse($this->userRepository->find($ucmId));
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

        return new JsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR);

    }
}
