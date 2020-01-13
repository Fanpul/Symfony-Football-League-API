<?php

declare(strict_types=1);

namespace App\DataTransformer;

use App\Entity\Team;
use App\Model\Input\TeamInput;
use App\Repository\LeagueRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class TeamTransformer implements DataTransformerInterface
{
    private $leagueRepository;
    private $normalizer;

    public function __construct(LeagueRepository $leagueRepository, GetSetMethodNormalizer $normalizer)
    {
        $this->leagueRepository = $leagueRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param TeamInput   $object
     * @param object|null $entity
     *
     * @return Team
     */
    public function transformToEntity($object, $entity = null): Team
    {
        if (null === $entity) {
            $entity = new Team();
        }

        if (!$entity instanceof Team) {
            throw new \InvalidArgumentException('Expected '.Team::class);
        }

        if ($object->getLeagueId()) {
            $league = $this->leagueRepository->findOneById($object->getLeagueId());
            if (null === $league) {
                throw new BadRequestHttpException('League not found, please check league id');
            }
            $entity->setLeague($league);
        }

        $entity->setName($object->getName());
        $entity->setStrip($object->getStrip());

        return $entity;
    }

    public function transformToModel($team)
    {
        return $this->normalizer->normalize($team, null, ['groups' => ['users']]);
    }
}
