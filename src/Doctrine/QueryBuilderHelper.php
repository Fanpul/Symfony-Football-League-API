<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class QueryBuilderHelper
{
    /**
     * On a query like $qb->select('users', 'u'), this would return 'u'.
     */
    public function getRootAlias(QueryBuilder $qb): string
    {
        $fromParts = $qb->getDQLPart('from');
        if (1 !== \count($fromParts)) {
            throw new \Exception('Query must have exactly one root alias to work with the QueryBuilderHelper');
        }

        return $qb->getDQLPart('from')[0]->getAlias();
    }

    /**
     * @var QueryBuilder
     * @var string       $joinFrom - the alias of the table you want to join from
     * @var string       $joinTo - the name of the table you want to to join to
     * @var string       $alias - the alias of the join; if the join already exists, the existing alias will be used; also if another join with that alias already exists, a different alias will be used
     *
     * @return string - the alias
     */
    public function join(QueryBuilder $qb, string $joinFrom, string $joinTo, string $alias): string
    {
        $knownAliases = [$this->getRootAlias($qb)];
        $joinParts = $qb->getDQLPart('join');

        if ($joinParts) {
            $joins = array_values($joinParts)[0];

            foreach ($joins as $join) {
                /** @var Join $join */
                // if the join already exists, return the alias
                // note this means you can't make multiple joins to the same table
                if (sprintf('%s.%s', $joinFrom, $joinTo) === $join->getJoin()) {
                    return $join->getAlias();
                }
                $knownAliases[] = $join->getAlias();
            }
        }

        // append the alias with a number to make sure it's unique
        $aliasAffix = 0;
        while (\in_array($alias, $knownAliases)) {
            ++$aliasAffix;
            $alias = sprintf('%s%d', $alias, $aliasAffix);
        }
        $qb->leftJoin(sprintf('%s.%s', $joinFrom, $joinTo), $alias);

        return $alias;
    }
}
