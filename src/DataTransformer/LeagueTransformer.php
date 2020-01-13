<?php

declare(strict_types=1);

namespace App\DataTransformer;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class LeagueTransformer implements DataTransformerInterface
{
    private $normalizer;

    public function __construct(GetSetMethodNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function transformToEntity($object, $entity = null)
    {
        throw new \RuntimeException('Method not implemented');
    }

    public function transformToModel($league)
    {
        return $this->normalizer->normalize($league, null, ['groups' => ['users']]);
    }
}
