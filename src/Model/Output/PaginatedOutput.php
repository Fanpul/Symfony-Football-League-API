<?php

declare(strict_types=1);

namespace App\Model\Output;

/**
 * Class PaginatedOutput.
 */
final class PaginatedOutput
{
    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var array
     */
    private $data = [];

    public function __construct()
    {
        $this->meta = new Meta();
    }

    public function getMeta(): Meta
    {
        return $this->meta;
    }

    public function setMeta(Meta $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
