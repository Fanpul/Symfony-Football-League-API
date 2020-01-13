<?php

declare(strict_types=1);

namespace App\Service\Search;

use App\Service\SearchEngine\AbstractSearchEngine;
use Doctrine\ORM\QueryBuilder;

final class LeagueSearchEngine extends AbstractSearchEngine
{
    protected function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('l');
        $queryBuilder->select('l');
//
//        $queryBuilder
////            ->innerJoin(LocationType::class, 'clt')
////            ->innerJoin(Country2::class, 'co')
//            ->where('l.locationType = clt.id')
//            ->andWhere('clt.country = co.id');

        return $queryBuilder;
    }
}
