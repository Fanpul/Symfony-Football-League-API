<?php

namespace App\Security;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ApiCodes;
use App\Service\JwtEncoder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class TokenAuthenticator
 * @package App\Security
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
{
    use ApiResponseTrait;

    private $jwtEncoder;
    private $em;

    /**
     * TokenAuthenticator constructor.
     * @param JwtEncoder $jwtEncoder
     * @param EntityManager $em
     */
    public function __construct(JwtEncoder $jwtEncoder, EntityManager $em)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->em = $em;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $token = $this->jwtEncoder->getCleanBearerToken($request);

        return ['token' => $token];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['token'];

        if (null === $token) {
            return null;
        }

        try {
            $data = $this->jwtEncoder->decodeJWT($token);
        } catch (UnauthorizedHttpException $e) {
            return null;
        }

        /**
         * @var $user User
         * @var $userRepository UserRepository
         */
        $userRepository = $this->em->getRepository(User::class);

        // auth_key check if token was refreshed
        $user = $userRepository->findOneBy(['id' => $data['jti'], 'auth_key' => sha1($token)]);

        // if a User object, checkCredentials() is called
        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->error = ApiCodes::ERR_ACCESS_TOKEN_INVALID;
        return $this->responseJson([], Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $this->error = ApiCodes::ERR_UNAUTHORIZED;
        return $this->responseJson([], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
