<?php

declare(strict_types=1);

namespace App\Service\SearchEngine;

use Doctrine\ORM\QueryBuilder;

interface SearchQueryPartInterface
{
    public function apply(QueryBuilder $qb, SearchModelInterface $search): void;

    public function isApplicable(SearchModelInterface $search): bool;
}
