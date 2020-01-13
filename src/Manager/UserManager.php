<?php

declare(strict_types=1);

namespace App\Manager;

use App\Model\Input\RefreshTokenInput;
use App\Service\UserService;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

class UserManager implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedServices(): array
    {
        return [
            UserService::class
        ];
    }

    public function refreshToken(RefreshTokenInput $input)
    {
        $this->getUserService()->refreshToken($input);
    }

    private function getUserService(): UserService
    {
        return $this->container->get(UserService::class);
    }
}
