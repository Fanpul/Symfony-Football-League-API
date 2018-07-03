<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use App\Exception\ApiException;
use App\Service\Validation\UserValidation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function loginAction(Request $request)
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        /**
         * @var $userService \App\Service\UserService
         * @var $jwtEncoder \App\Service\JwtEncoder
         */
        $userService = $this->get('user_service');
        $jwtEncoder = $this->get('jwt_encoder');

        [$token, $refreshToken] = $userService->login($user);

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
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
    public function refreshTokenAction(Request $request)
    {
        /**
         * @var $userService \App\Service\UserService
         * @var $jwtEncoder \App\Service\JwtEncoder
         */
        $userService = $this->get('user_service');
        $jwtEncoder = $this->get('jwt_encoder');

        /**
         * @var $validation UserValidation
         */
        $validation = $this->get('validation.user_service');

        // validate fields
        if (!$validation->validateRefreshToken($request)) {
            $this->errorFields = $validation->getErrorFields();
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $params = [
            'access_token' => $jwtEncoder->getCleanBearerToken($request->headers->get('Authorization')),
            'refresh_token' => $request->get('refresh_token')
        ];

        try {
            /** @var $user User */
            [$user, $token, $refreshToken] = $userService->refreshToken($params);

            return $this->responseJson([
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'exp' => $jwtEncoder->getPayload($token, 'exp'),
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername()
                ]
            ]);
        } catch (ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
        }
    }

}
