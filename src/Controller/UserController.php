<?php

namespace App\Controller;

use App\Entity\User;
use App\Manager\OfferManager;
use App\Manager\UserManager;
use App\Repository\OfferRepository;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use mysql_xdevapi\Exception;
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
     * @var UserManager
     */
    private $userManager;

    /**
     * @var OfferManager
     */
    private $offerManager;

    private $serializer;

    /**
     * UserController constructor.
     * @param UserManager $userManager
     * @param OfferManager $offerManager
     * @param SerializerInterface $serializer
     */
    public function __construct(UserManager $userManager, OfferManager $offerManager, SerializerInterface $serializer)
    {
        $this->userManager = $userManager;
        $this->offerManager = $offerManager;
        $this->serializer = $serializer;
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
     * @return JsonResponse
     */
    public function profile()
    {
        try {
            $profile = $this->userManager->findProfile($this->getUser());
            return $this->getJsonResponse($profile['data'], $profile['status']);
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/user", name="update.user", methods={"PUT"})
     * @param Request $request
     * @param ValidatorInterface $validator
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
    public function updateProfil(Request $request, ValidatorInterface $validator)
    {
        $user = $this->getUnserializedUser($request);

        $errors = $validator->validate($user, null, ["profil"]);

        if(count($errors)){
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $token = $this->userManager->updateUser($this->getUser(), $user);
            return $this->getJsonResponse($token, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/user/password", name="update.userpassword", methods={"PUT"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $passwordEncoder
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
        $request = json_decode($request->getContent(), true);

        $newPassword = $request["new_password"];

        $currentUser = $this->userManager->findById($this->getUser());

        $currentUser->setPassword($newPassword);

        $errors = $validator->validate($currentUser, null, ["password"]);

        if(count($errors)) {
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $newPassword = $passwordEncoder->encodePassword($currentUser, $newPassword);
        $currentUser->setPassword($newPassword);

        try {
            $this->userManager->updatePassword();
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }

        return $this->getJsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/register", name="create.user", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $passwordEncoder
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

        $errors = $validator->validate($user, null, ['registration']);

        if(count($errors)){
            return $this->getJsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);

        try {
            $this->userManager->createUser($user);
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }

        return new JsonResponse(null, Response::HTTP_CREATED, ["Link" => "http://localhost/api/login"/*$this->generateUrl("api_login", null, 0)*/]);
    }

    /**
     * @Route("/user/addoffer", name="add.offer.to.user", methods={"PUT"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addOfferToUser(Request $request) {
        $offer = json_decode($request->getContent(), true);

        try {
            $this->userManager->addOfferToUser($this->getUser(), $offer["code"]);
        } catch (\Exception $e) {
            return $this->getJsonResponse($e->getMessage(), $e->getCode());
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getUnserializedUser(Request $request){
        return $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );
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
