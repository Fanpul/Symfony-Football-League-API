<?php

declare(strict_types=1);

namespace App\Service\Search\Filters;

use App\Service\SearchEngine\SearchModelInterface;
use App\Service\SearchEngine\SearchQueryPartInterface;
use Doctrine\ORM\QueryBuilder;

class Limit implements SearchQueryPartInterface
{
    const DEFAULT_LIMIT_VALUE = 10;

    public function apply(QueryBuilder $qb, SearchModelInterface $search): void
    {
        $qb->setMaxResults($search->limit ?? self::DEFAULT_LIMIT_VALUE);
    }

    public function isApplicable(SearchModelInterface $search): bool
    {
        return true;
    }
}
