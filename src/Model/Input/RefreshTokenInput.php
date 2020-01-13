<?php

declare(strict_types=1);

namespace App\Model\Input;

class RefreshTokenInput implements InputModelInterface
{
    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * @return null|string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param null|string $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return null|string
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param null|string $refreshToken
     */
    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }
}
