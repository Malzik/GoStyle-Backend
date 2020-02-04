<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class UserController
 * @package App\Controller
 * @Route("/api")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    private $serializer;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user", name="profil", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns connected profi and his offers",
     *     @Model(type=User::class)
     * )
     * @SWG\Tag(name="Users")
     */
    public function profil()
    {
        $profil = $this->userRepository->find(163);
        if(empty($profil))
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        return new JsonResponse($profil);
    }

    /**
     * @Route("/user", name="update.profil", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     * * @SWG\Response(
     *     response=204,
     *     description="Update Profil",
     * )
     * @SWG\Parameter(
     *     name="User",
     *     in="body",
     *     description="The field used to update user",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="first_name", type="string"),
     *         @SWG\Property(property="last_name", type="string"),
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Tag(name="Users")
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
     * @SWG\Response(
     *     response=201,
     *     description="Register new profil",
     * )
     * @SWG\Parameter(
     *     name="User",
     *     in="body",
     *     description="The field used to create user",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="first_name", type="string"),
     *         @SWG\Property(property="last_name", type="string"),
     *         @SWG\Property(property="email", type="string"),
     *         @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Tag(name="Users")
     */
    public function createUser(Request $request, ValidatorInterface $validator)
    {
        $user = $this->getUnserializedUser($request);

        $errors = $validator->validate($user);

        if(count($errors)){
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED, ["Link" => "http://localhost/api/login"/*$this->generateUrl("api_login", null, 0)*/]);
    }

    private function getUnserializedUser(Request $request){
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );
        return $user;
    }

    private function getJsonResponse($data = null, int $status = 200, $headers = []) : JsonResponse
    {
        $serializedData = $data;
        if (!is_null($data)){
            $serializedData = $this->serializer->serialize($data, 'json');
        }
        $response = new JsonResponse(null, $status, $headers);
        $response->setContent($serializedData);

        return $response;
    }


}
