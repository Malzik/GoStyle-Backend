<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LoginController
 * @Route("/api")
 * @package App\Controller
 */
class LoginController extends AbstractController
{
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
