<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OfferRepository;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository, OfferRepository $offerRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->offerRepository = $offerRepository;
    }

    /**
     * @Route("/user", name="user", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns connected profi and his offers",
     *     @Model(type=User::class)
     * )
     * @SWG\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function profil(Request $request)
    {
        $profil = $this->userRepository->find($this->getUser()->getId());
        if(empty($profil))
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        return $this->getJsonResponse($profil);
    }

    /**
     * @Route("/user", name="update.user", methods={"PUT"})
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
     * @Security(name="Bearer")
     */
    public function updateProfil(Request $request, ValidatorInterface $validator, JWTTokenManagerInterface $JWTTokenManager)
    {
        $user = $this->getUnserializedUser($request);
        $currentUser = $this->userRepository->find($this->getUser()->getId());
        if(empty($currentUser))
            return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);

        $errors = $validator->validate($user, null, ["profil"]);

        if(count($errors)){
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $currentUser->setFirstName($user->getFirstName());
        $currentUser->setLastName($user->getLastName());
        $currentUser->setEmail($user->getEmail());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $token = $JWTTokenManager->create($currentUser);
        return new JsonResponse(["token" => $token], Response::HTTP_OK, ["Link" => "http://localhost/api/login"]);
    }

    /**
     * @Route("/user/password", name="update.userpassword", methods={"PUT"})
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
    public function updatePassword(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $currentUser = $this->userRepository->find($this->getUser()->getId());
        if(empty($currentUser))
            return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);

        $request = json_decode($request->getContent(), true);

        $newPassword = $request["new_password"];

        $newPassword = $passwordEncoder->encodePassword($currentUser, $newPassword);
        $currentUser->setPassword($newPassword);
        $errors = $validator->validate($currentUser, null, ["password"]);

        if(count($errors)) {
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, ["Link" => "http://localhost/api/login"]);
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
     * @Security(name="Bearer")
     */
    public function createUser(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
        /**
         * @var User $user
         */
        $user = $this->getUnserializedUser($request);
        if(empty($user))
            return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);

        $errors = $validator->validate($user, null, ['registration']);

        if(count($errors)){
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }
        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED, ["Link" => "http://localhost/api/login"/*$this->generateUrl("api_login", null, 0)*/]);
    }

    /**
     * @Route("/user/addoffer", name="add.offer.to.user", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addOfferToUser(Request $request) {
        $currentUser = $this->userRepository->find($this->getUser()->getId());
        $offer = json_decode($request->getContent(), true);
        $offer = $this->offerRepository->findOneByCode($offer["code"]);

        $currentUser->addOffer($offer);
        $em = $this->getDoctrine()->getManager();
        $em->persist($currentUser);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
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
