<?php

declare(strict_types=1);

namespace App\DataTransformer;

interface DataTransformerInterface
{
    /**
     * @param mixed $object
     * @param mixed $entity
     *
     * @return mixed
     */
    public function transformToEntity($object, $entity = null);

    /**
     * @param mixed $object
     *
     * @return mixed
     */
    public function transformToModel($object);
}
