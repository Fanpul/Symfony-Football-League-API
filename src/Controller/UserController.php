<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ApiCodes;
use App\Service\jwtEncoder;
use App\Controller\Traits\ApiResponseTrait;

class UserController extends Controller
{
    use ApiResponseTrait;

    /**
     * @Route("/v1/login", name="v1_user_login")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        /**
         * @var $jwtEncoder jwtEncoder
         */
        $jwtEncoder = $this->get('jwt_encoder');

        $token = $jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $user->getRefreshToken(),
            'exp' => $jwtEncoder->getPayload($token, 'exp'),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ]
        ]);
    }

    /**
     * @Route("/v1/refresh-token", name="v1_user_refresh_login")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->get('refresh_token');

        if (empty($refreshToken)) {
            $this->errorFields['refresh_token'] = ApiCodes::ERR_REQUIRED_PARAM;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var $jwtEncoder jwtEncoder
         */
        $jwtEncoder = $this->get('jwt_encoder');

        if (!$jwtEncoder->validateRefreshToken($refreshToken)) {
            $this->error = ApiCodes::ERR_REFRESH_TOKEN_INVALID;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $currentToken = $jwtEncoder->getCleanBearerToken($request);

        /**
         * @var $userRepository UserRepository
         */
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository(User::class);

        $user = $userRepository->loadUserByJwt($currentToken);

        if (empty($user)) {
            $this->error = ApiCodes::ERR_ACCESS_TOKEN_INVALID;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        if ($refreshToken != $user->getRefreshToken()) {
            $this->error = ApiCodes::ERR_REFRESH_TOKEN_INVALID;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $token = $jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $em->persist($user);
        $em->flush();

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $user->getRefreshToken(),
            'exp' => $jwtEncoder->getPayload($token, 'exp'),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ]
        ]);
    }

}
