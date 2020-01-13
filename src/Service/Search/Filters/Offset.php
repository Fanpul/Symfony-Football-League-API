<?php

declare(strict_types=1);

namespace App\Service\Search\Filters;

use App\Service\SearchEngine\SearchModelInterface;
use App\Service\SearchEngine\SearchQueryPartInterface;
use Doctrine\ORM\QueryBuilder;

class Offset implements SearchQueryPartInterface
{
    const DEFAULT_OFFSET_VALUE = 0;

    public function apply(QueryBuilder $qb, SearchModelInterface $search): void
    {
        $qb->setFirstResult($search->offset ?? self::DEFAULT_OFFSET_VALUE);
    }

    public function isApplicable(SearchModelInterface $search): bool
    {
        return true;
    }
}
