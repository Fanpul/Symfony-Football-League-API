<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\ApiException;
use App\Model\Input\RefreshTokenInput;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
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
    public function getJWT(User $user)
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
     * @param RefreshTokenInput $input
     * @return array
     * @throws ApiException
     */
    public function refreshToken(RefreshTokenInput $input)
    {
        /**
         * @var $userRepository UserRepository
         */
        $userRepository = $this->em->getRepository(User::class);

        $user = $userRepository->loadUserByJwt($input->getToken());
        if (empty($user)) {
            $message = ApiCodes::getMessage(ApiCodes::ERR_ACCESS_TOKEN_INVALID);
            throw new ApiException($message, Response::HTTP_BAD_REQUEST);
        }

        if ($input->getRefreshToken() != $user->getRefreshToken()) {
            $message = ApiCodes::getMessage(ApiCodes::ERR_REFRESH_TOKEN_INVALID);
            throw new ApiException($message, Response::HTTP_BAD_REQUEST);
        }

        $token = $this->jwtEncoder->getJWT(['jti' => $user->getId()]);
        $refreshToken = $this->jwtEncoder->generateRefreshToken();

        $user->setAuthKey($token);
        $user->setRefreshToken($refreshToken);

        $this->em->persist($user);
        $this->em->flush();

        return [$token, $refreshToken];
    }

}
