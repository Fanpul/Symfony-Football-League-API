<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @param $criteria
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function findByCriteria($criteria = [], $limit = 10, $offset = 0)
    {
        $qb = $this->createQueryBuilder('t')->select();
        $qb->leftJoin('t.league', 'l');

        // filter by league_id
        if (!empty($criteria['league_id'])) {
            $qb
                ->andWhere('l.id = :league_id')
                ->setParameter('league_id', $criteria['league_id']);
        }

        $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $criteria
     * @return mixed
     */
    public function findCountByCriteria($criteria = [])
    {
        $qb = $this->createQueryBuilder('t')->select('count(t.id)');
        $qb->leftJoin('t.league', 'l');

        // filter by league_id
        if (!empty($criteria['league_id'])) {
            $qb
                ->andWhere('l.id = :league_id')
                ->setParameter('league_id', $criteria['league_id']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
