<?php

declare(strict_types=1);

namespace App\Service\Search\Filters\League;

use App\Doctrine\QueryBuilderHelper;
use App\Service\SearchEngine\SearchModelInterface;
use App\Service\SearchEngine\SearchQueryPartInterface;
use Doctrine\ORM\QueryBuilder;

class Name implements SearchQueryPartInterface
{
    private $helper;

    public function __construct(QueryBuilderHelper $helper)
    {
        $this->helper = $helper;
    }

    public function apply(QueryBuilder $qb, SearchModelInterface $search): void
    {
        $qb
            ->andWhere(sprintf('(%s.name like :name)', $this->helper->getRootAlias($qb)))
            ->setParameter('name', '%'.$search->getName().'%');
    }

    public function isApplicable(SearchModelInterface $search): bool
    {
        return (!empty($search->getName()) && strlen($search->getName()) >= 3) ?? false;
    }
}
