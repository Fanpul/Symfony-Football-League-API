<?php

declare(strict_types=1);

namespace App\Service\SearchEngine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

abstract class AbstractSearchEngine implements SearchEngineInterface
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var SearchQueryPartInterface[]
     */
    private $queryParts = [];

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function addQueryPart(SearchQueryPartInterface $queryPart): void
    {
        $this->queryParts[] = $queryPart;
    }

    public function setQueryParts(array $queryParts): void
    {
        $this->queryParts = [];

        foreach ($queryParts as $item) {
            $this->addQueryPart($item);
        }
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function search(SearchModelInterface $search): Paginator
    {
        $queryBuilder = $this->createQueryBuilder();

//        $firstResult = ($search->getPage() - 1) * $search->getLimit();
        $queryBuilder->setFirstResult($search->getOffset());
        $queryBuilder->setMaxResults($search->getLimit());

        $this->applySearchFilters($queryBuilder, $search);

        return new Paginator($queryBuilder);
    }

    abstract protected function createQueryBuilder(): QueryBuilder;

    private function applySearchFilters(QueryBuilder $queryBuilder, SearchModelInterface $search): void
    {
        /** @var SearchQueryPartInterface $part */
        foreach ($this->queryParts as $part) {
            if ($part->isApplicable($search)) {
                $part->apply($queryBuilder, $search);
            }
        }
    }
}
