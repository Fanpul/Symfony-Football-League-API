<?php

declare(strict_types=1);

namespace App\Service\Search\Filters;

use App\Doctrine\QueryBuilderHelper;
use App\Service\SearchEngine\SearchModelInterface;
use App\Service\SearchEngine\SearchQueryPartInterface;
use Doctrine\ORM\QueryBuilder;

class League implements SearchQueryPartInterface
{
    private $helper;

    public function __construct(QueryBuilderHelper $helper)
    {
        $this->helper = $helper;
    }

    public function apply(QueryBuilder $qb, SearchModelInterface $search): void
    {
        $leagueAlias = $this->helper->join($qb, $this->helper->getRootAlias($qb), 'league', 'l');
        $qb->addSelect('l');

        $qb
            ->andWhere(sprintf('%s.id = :leagueId', $leagueAlias))
            ->setParameter('leagueId', $search->getLeagueId());

//        $qb->andWhere($leagueAlias.'.visible = 1');
    }

    public function isApplicable(SearchModelInterface $search): bool
    {
        return !empty($search->getLeagueId()) ?? false;
    }
}
