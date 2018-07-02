<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param $username
     * @return User|null
     */
    public function loadUserByUsername($username)
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * @param $authKey
     * @return User|null
     */
    public function loadUserByAuthKey($authKey)
    {
        return $this->findOneBy(['auth_key' => $authKey]);
    }

    /**
     * @param $token
     * @return User|null
     */
    public function loadUserByJwt($token)
    {
        return $this->loadUserByAuthKey(sha1($token));
    }
}
