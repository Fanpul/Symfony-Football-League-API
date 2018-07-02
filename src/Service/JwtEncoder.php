<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtEncoder
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     *
     * @param  array $payloads payloads data to set, default value is empty array.
     * See registered claim names for payloads at https://tools.ietf.org/html/rfc7519#section-4.1
     * @return string encoded JWT
     */
    public function getJWT(array $payloads = [])
    {
        $secret = $this->getSecretKey();
        $token = array_merge($payloads, static::getHeaderToken());

        if (!isset($token['exp'])) {
            $token['exp'] = time() + $this->getTokenExpire();
        }
        return JWT::encode($token, $secret, static::getAlgorithm());
    }

    /**
     * Getter for encryption algorithm used in JWT generation and decoding
     * Override this method to set up other algorithm.
     *
     * @return string needed algorithm
     */
    public function getAlgorithm()
    {
        return $this->container->getParameter('algorithmJWT');
    }

    /**
     * Returns token expire period
     *
     * @return int
     */
    public function getTokenExpire()
    {
        return 3600 * 24 * $this->container->getParameter('tokenExpireDays');
    }

    /**
     * Returns refresh token expire period
     *
     * @return int
     */
    public function getRefreshTokenExpire()
    {
        return 3600 * 24 * $this->container->getParameter('refreshTokenExpireDays');
    }

    /**
     * @return string
     */
    public function generateRefreshToken()
    {
        $expiredTime = time() + $this->getRefreshTokenExpire();
        return base64_encode(md5(time()) . md5(rand(1000, 9999)) . '_' . $expiredTime);
    }

    /**
     * Validates user token
     *
     * @param string $refreshToken
     * @return bool
     */
    public function validateRefreshToken($refreshToken)
    {
        $refreshToken = base64_decode($refreshToken);

        $expiredTime = strstr($refreshToken, '_');
        $expiredTime = str_replace('_', '', $expiredTime);

        return (int)$expiredTime > time();
    }

    /**
     * Getter for secret key that's used for generation of JWT
     *
     * @return string secret key used to generate JWT
     */
    protected function getSecretKey(): string
    {
        // get value of a parameter
        return $this->container->getParameter('secretJWT');
    }

    /**
     * Getter for "header" array that's used for generation of JWT
     *
     * @return array JWT Header Token param, see http://jwt.io/ for details
     */
    protected function getHeaderToken(): array
    {
        return [
            'typ' => 'JWT',
            'alg' => $this->getAlgorithm()
        ];
    }

    /**
     * Decode JWT token
     *
     * @param string $token access token to decode
     * @param bool $exception
     * @throws UnauthorizedHttpException
     * @return array
     */
    public function decodeJWT(string $token, $exception = true)
    {
        $secret = $this->getSecretKey();
        $errorText = 'Incorrect token';

        try {
            $decoded = JWT::decode($token, $secret, [$this->getAlgorithm()]);
        } catch (\Exception $e) {

            if ($exception) {
                if ($e->getMessage() == 'Expired token') {
                    $errorText = 'Expired token';
                }
                throw new UnauthorizedHttpException('Bearer realm="api"', $errorText);
            }
            return null;
        }

        return (array)$decoded;
    }

    /**
     * Get payload data in a JWT string
     *
     * @param string $token
     * @param string|null $payloadId Payload ID that want to return,
     * the default value is NULL. If NULL it will return all the payloads data
     * @return mixed payload data
     */
    public function getPayload($token, $payloadId = null)
    {
        $decodedArray = $this->decodeJWT($token);
        if (!empty($payloadId)) {
            return $decodedArray[$payloadId] ?? null;
        }

        return $decodedArray;
    }

    /**
     * Return clean Bearer Token
     *
     * @param Request $request
     * @return null
     */
    public function getCleanBearerToken(Request $request)
    {
        $bearerToken = $request->headers->get('Authorization');
        if (!empty($bearerToken) && preg_match("/^Bearer\\s+(.*?)$/", $bearerToken, $matches)) {
            return $matches[1] ?? null;
        }

        return null;
    }

}
