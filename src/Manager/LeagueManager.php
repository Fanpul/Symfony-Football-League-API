<?php

declare(strict_types=1);

namespace App\Manager;

use App\DataTransformer\LeagueTransformer;
use App\Factory\PaginatedResultFactory;
use App\Model\Input\LeaguesSearch;
use App\Model\Output\PaginatedOutput;
use App\Repository\LeagueRepository;
use App\Service\ApiCodes;
use App\Service\Search\LeagueSearchEngine;
use App\Service\Search\TeamSearchEngine;
use Doctrine\ORM\EntityManagerInterface;
//use http\Exception\RuntimeException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LeagueManager implements ServiceSubscriberInterface
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
            LeagueSearchEngine::class,
            LeagueTransformer::class,
            'app.factory.paginated_result.league' => PaginatedResultFactory::class,
            LeagueRepository::class,
            EntityManagerInterface::class,
        ];
    }

    public function findAll(LeaguesSearch $search): PaginatedOutput
    {
        $paginator = $this->getSearchEngine()->search($search);

        return $this->getPaginatedResultFactory()->createResult($search, $paginator);
    }

    public function remove(int $id): void
    {
        $leagueEntity = $this->getRepository()->find($id);

        if (!$leagueEntity) {
            throw new NotFoundHttpException(ApiCodes::getMessage(ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND));
        }

        $this->getEntityManager()->remove($leagueEntity);
        $this->getEntityManager()->flush();
    }

    private function getSearchEngine(): LeagueSearchEngine
    {
        return $this->container->get(LeagueSearchEngine::class);
    }

    private function getPaginatedResultFactory(): PaginatedResultFactory
    {
        return $this->container->get('app.factory.paginated_result.league');
    }

    private function getRepository(): LeagueRepository
    {
        return $this->container->get(LeagueRepository::class);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }
}
