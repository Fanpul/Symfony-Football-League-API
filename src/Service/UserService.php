<?php

namespace App\Service;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
    use ApiResponseTrait;

    private $em = null;
    private $jwtEncoder = null;

    public function __construct(EntityManagerInterface $em, JwtEncoder $jwtEncoder)
    {
        $this->em = $em;
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function login(User $user)
    {
        $token = $this->jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $this->jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $this->em->persist($user);
        $this->em->flush();

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $user->getRefreshToken(),
            'exp' => $this->jwtEncoder->getPayload($token, 'exp'),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return bool|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function validateRefreshToken(Request $request)
    {
        $refreshToken = $request->get('refresh_token');

        if (empty($refreshToken)) {
            $this->errorFields['refresh_token'] = ApiCodes::ERR_REQUIRED_PARAM;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->jwtEncoder->validateRefreshToken($refreshToken)) {
            $this->error = ApiCodes::ERR_REFRESH_TOKEN_INVALID;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }
        return true;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->get('refresh_token');

        $currentToken = $this->jwtEncoder->getCleanBearerToken($request);

        /**
         * @var $userRepository UserRepository
         */
        $userRepository = $this->em->getRepository(User::class);

        $user = $userRepository->loadUserByJwt($currentToken);
        if (empty($user)) {
            $this->error = ApiCodes::ERR_ACCESS_TOKEN_INVALID;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        if ($refreshToken != $user->getRefreshToken()) {
            $this->error = ApiCodes::ERR_REFRESH_TOKEN_INVALID;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $token = $this->jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $this->jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $this->em->persist($user);
        $this->em->flush();

        return $this->responseJson([
            'access_token' => $token,
            'refresh_token' => $user->getRefreshToken(),
            'exp' => $this->jwtEncoder->getPayload($token, 'exp'),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ]
        ]);
    }


}