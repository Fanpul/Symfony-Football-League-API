<?php

declare(strict_types=1);

namespace App\Manager;

use App\DataTransformer\TeamTransformer;
use App\Exception\EntityValidationException;
use App\Factory\PaginatedResultFactory;
use App\Model\Input\TeamInput;
use App\Model\Input\TeamsSearch;
use App\Model\Output\PaginatedOutput;
use App\Repository\TeamRepository;
use App\Service\ApiCodes;
use App\Service\Search\TeamSearchEngine;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamManager implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedServices(): array
    {
        return [
            TeamSearchEngine::class,
            'app.factory.paginated_result.team' => PaginatedResultFactory::class,
            TeamTransformer::class,
            EntityManagerInterface::class,
            ValidatorInterface::class,
            TeamRepository::class,
        ];
    }

    public function findAll(TeamsSearch $search): PaginatedOutput
    {
        $paginator = $this->getSearchEngine()->search($search);

        return $this->getPaginatedResultFactory()->createResult($search, $paginator);
    }

    public function find(int $id): array
    {
        $entity = $this->getRepository()->findOneById($id);
        if (!$entity) {
            throw new NotFoundHttpException(ApiCodes::ERR_CODE_TEAM_NOT_FOUND);
        }

        return $this->getTransformer()->transformToModel($entity);
    }

    public function create(TeamInput $input): array
    {
        $entity = $this->getTransformer()->transformToEntity($input);

        $violations = $this->getValidator()->validate($entity);
        if ($violations->count() > 0) {
            throw new EntityValidationException($violations);
        }

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $this->getTransformer()->transformToModel($entity);
    }

    public function update(int $id, TeamInput $input): array
    {
        $entity = $this->getRepository()->findOneById($id);
        if (!$entity) {
            throw new NotFoundHttpException(ApiCodes::ERR_CODE_TEAM_NOT_FOUND);
        }

        $entity = $this->getTransformer()->transformToEntity($input, $entity);

        $violations = $this->getValidator()->validate($entity);
        if ($violations->count() > 0) {
            throw new EntityValidationException($violations);
        }

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $this->getTransformer()->transformToModel($entity);
    }

    private function getSearchEngine(): TeamSearchEngine
    {
        return $this->container->get(TeamSearchEngine::class);
    }

    private function getPaginatedResultFactory(): PaginatedResultFactory
    {
        return $this->container->get('app.factory.paginated_result.team');
    }

    private function getTransformer(): TeamTransformer
    {
        return $this->container->get(TeamTransformer::class);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->container->get(ValidatorInterface::class);
    }

    private function getRepository(): TeamRepository
    {
        return $this->container->get(TeamRepository::class);
    }
}
