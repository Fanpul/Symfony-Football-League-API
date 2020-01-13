<?php

declare(strict_types=1);

namespace App\Factory;

use App\DataTransformer\DataTransformerInterface;
use App\Model\Output\PaginatedOutput;
use App\Service\SearchEngine\SearchModelInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatedResultFactory
{
    /**
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    public function __construct(DataTransformerInterface $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
    }

    public function createResult(SearchModelInterface $request, Paginator $paginator): PaginatedOutput
    {
        $entities = [];
        foreach ($paginator->getIterator() as $result) {
            $entities[] = $this->dataTransformer->transformToModel($result);
        }

        $result = new PaginatedOutput();
        $result->getMeta()
            ->setOffset($request->getOffset())
            ->setLimit($request->getLimit())
            ->setTotal($paginator->count());

        $result->setData($entities);

        return $result;
    }
}
