<?php

declare(strict_types=1);

namespace App\Service\Search;

use App\Service\SearchEngine\AbstractSearchEngine;
use Doctrine\ORM\QueryBuilder;

final class TeamSearchEngine extends AbstractSearchEngine
{
    protected function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('t');
        $queryBuilder->select('t');

        return $queryBuilder;
    }
}
