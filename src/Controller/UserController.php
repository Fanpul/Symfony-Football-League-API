<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\User;
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
    public function loginAction(Request $request)
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        /**
         * @var $userService \App\Service\UserService
         */
        $userService = $this->get('user_service');

        return $userService->login($user);
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
         */
        $userService = $this->get('user_service');

        $validate = $userService->validateRefreshToken($request);

        if ($validate instanceof Response) {
            return $validate;
        }

        return $userService->refreshToken($request);
    }

}
