<?php

declare(strict_types=1);

namespace App\Service\SearchEngine;

use Doctrine\ORM\Tools\Pagination\Paginator;

interface SearchEngineInterface
{
    public function search(SearchModelInterface $search): Paginator;
}
