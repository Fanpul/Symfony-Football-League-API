<?php

declare(strict_types=1);

namespace App\Model\Input;

use App\Model\Request\RequestQueryInterface;
use App\Service\SearchEngine\SearchModelInterface;

class TeamsSearch implements SearchModelInterface, RequestQueryInterface
{
    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @var int|null
     */
    private $leagueId;

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getLeagueId(): int
    {
        return $this->leagueId;
    }

    /**
     * @param int|null $leagueId
     */
    public function setLeagueId(int $leagueId)
    {
        $this->leagueId = $leagueId;
    }
}
