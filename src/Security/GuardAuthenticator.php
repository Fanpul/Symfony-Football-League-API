<?php

namespace App\Security;

use App\Controller\Traits\ApiResponseTrait;
use App\Model\Input\LoginInput;
use App\Service\ApiCodes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class GuardAuthenticator
 * @package App\Security
 */
class GuardAuthenticator extends AbstractGuardAuthenticator
{
    use ApiResponseTrait;

    private $passwordEncoder;
    private $serializer;

    public function __construct(UserPasswordEncoder $passwordEncoder, SerializerInterface $serializer)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request)
    {
        $object = $this->serializer->deserialize(
            $request->getContent(),
            LoginInput::class,
            $request->getContentType(),
            []
        );

        return $object;

//        return [
//            'username' => $object->getLogin(),
//            'password' => $object->getPassword(),
//        ];
//
//        return [
//            'username' => $request->get('username'),
//            'password' => $request->get('password'),
//        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
//        echo '<pre>';
//        print_r($credentials);
//        echo '</pre>';
//        exit;

        $username = $credentials->getLogin();

//        $username = $credentials['username'] ?? null;

        if (empty($username)) {
            return null;
        }

        // if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($username);
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
//        echo '<pre>';
//        print_r($credentials);
//        echo '</pre>';
//        exit;

//        $passwordPlain = $credentials['password'] ?? null;
        $passwordPlain = $credentials->getPassword();

        if (empty($passwordPlain)) {
            return false;
        }

        // check credentials - e.g. make sure the password is valid
        if (!$this->passwordEncoder->isPasswordValid($user, $passwordPlain)) {
            return false;
        }

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

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // $message = ApiCodes::getMessage(ApiCodes::ERR_INVALID_CREDENTIALS);
        $data = [
            'code' => Response::HTTP_FORBIDDEN,
            'message' => $exception->getMessageKey(),
            'errors' => []
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = ApiCodes::getMessage(ApiCodes::ERR_UNAUTHORIZED);
        $data = [
            'code' => Response::HTTP_UNAUTHORIZED,
            'message' => $message,
            'errors' => []
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
