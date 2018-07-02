<?php

namespace App\Service;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use App\Exception\ApiException;
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
     * @return array
     */
    public function login(User $user)
    {
        $token = $this->jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $this->jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $this->em->persist($user);
        $this->em->flush();

        return [$token, $refreshToken];
    }

    /**
     * @param Request $request
     * @return array
     * @throws ApiException
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
            throw new ApiException(ApiCodes::ERR_ACCESS_TOKEN_INVALID, Response::HTTP_BAD_REQUEST);
        }

        if ($refreshToken != $user->getRefreshToken()) {
            throw new ApiException(ApiCodes::ERR_REFRESH_TOKEN_INVALID, Response::HTTP_BAD_REQUEST);
        }

        $token = $this->jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $this->jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $this->em->persist($user);
        $this->em->flush();

        return [$user, $token, $refreshToken];
    }

}
