<?php

namespace App\Service;

use App\Entity\League;
use App\Entity\Team;
use App\Exception\ApiException;
use App\Repository\LeagueRepository;
use App\Repository\TeamRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class TeamService
{
    private $em = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $params
     * @return array
     * @throws ApiException
     */
    public function getPaginationData($params)
    {
        $conditions = [];
        if (!empty($params['league_id'])) {
            $conditions['league_id'] = $params['league_id'];
        }

        /**
         * @var $teamRepository TeamRepository
         */
        $teamRepository = $this->em->getRepository(Team::class);

        // find data
        $teamEntities = $teamRepository->findByCriteria($conditions, $params['limit'], $params['offset']);

        if (empty($teamEntities)) {
            throw new ApiException(ApiCodes::ERR_DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        // find total count
        $totalCount = $teamRepository->findCountByCriteria($conditions);

        return [$teamEntities, $totalCount];
    }

    /**
     * @param $params
     * @return Team
     * @throws ApiException
     */
    public function create($params)
    {
        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        // find league
        $league = $leagueRepository->find($params['league_id']);
        if (empty($league)) {
            throw new ApiException(ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND, Response::HTTP_BAD_REQUEST);
        }

        // create team
        $team = new Team();
        $team->setName($params['name']);
        $team->setStrip($params['strip']);
        $team->setLeague($league);

        try {
            $this->em->persist($team);
            $this->em->flush();

            return $team;
        } catch (UniqueConstraintViolationException $e) {
            throw new ApiException(ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param $id
     * @param $params
     * @return Team|null
     * @throws ApiException
     */
    public function update($id, $params)
    {
        /** @var $teamRepository TeamRepository */
        $teamRepository = $this->em->getRepository(Team::class);

        $teamEntity = $teamRepository->find($id);
        if (empty($teamEntity)) {
            throw new ApiException(ApiCodes::ERR_CODE_TEAM_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if (!empty($params['name'])) {
            $teamEntity->setName($params['name']);
        }
        if (!empty($params['strip'])) {
            $teamEntity->setStrip($params['strip']);
        }
        if (!empty($params['league_id'])) {
            /** @var $leagueRepository LeagueRepository */
            $leagueRepository = $this->em->getRepository(League::class);

            // find league
            $league = $leagueRepository->find($params['league_id']);
            if (empty($league)) {
                throw new ApiException(ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND, Response::HTTP_BAD_REQUEST);
            }
            $teamEntity->setLeague($league);
        }

        try {
            $this->em->persist($teamEntity);
            $this->em->flush();

            return $teamEntity;
        } catch (UniqueConstraintViolationException $e) {
            throw new ApiException(ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME, Response::HTTP_BAD_REQUEST);
        }
    }

}
