<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use App\Exception\ApiException;
use App\Manager\UserManager;
use App\Model\Input\RefreshTokenInput;
use App\Service\UserService;
use App\Service\Validation\UserValidation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    use ApiResponseTrait;

    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * @Route("/v1/login", name="v1_user_login", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function loginAction()
    {
//        /**@var $userService \App\Service\UserService */
//        $userService = $this->container->get('user_service');

        [$token, $refreshToken] = $this->service->getJWT($this->getUser());

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @Route("/v1/refresh-token", name="v1_user_refresh_login", methods={"POST"})
     *
     * @param RefreshTokenInput $input
     * @return JsonResponse
     * @throws ApiException
     */
    public function refreshTokenAction(RefreshTokenInput $input)
    {
        [$token, $refreshToken] = $this->service->refreshToken($input);

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
        ]);
    }

}
