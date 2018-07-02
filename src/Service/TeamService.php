<?php

namespace App\Service;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\League;
use App\Entity\Team;
use App\Exception\ApiException;
use App\Repository\LeagueRepository;
use App\Repository\TeamRepository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TeamService
{
    use ApiResponseTrait {
        getRequestParams as _getRequestParams;
    }

    private $em = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPaginationList(Request $request)
    {
        $this->getRequestParams($request);

        $conditions = [];
        if (!empty($this->requestParams['league_id'])) {
            $conditions['league_id'] = $this->requestParams['league_id'];
        }

        /**
         * @var $teamRepository TeamRepository
         */
        $teamRepository = $this->em->getRepository(Team::class);

        // find data
        return $teamRepository->findByCriteria($conditions, $this->requestParams['limit'], $this->requestParams['offset']);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPaginationCount(Request $request)
    {
        $this->getRequestParams($request);

        $conditions = [];
        if (!empty($this->requestParams['league_id'])) {
            $conditions['league_id'] = $this->requestParams['league_id'];
        }

        /**
         * @var $teamRepository TeamRepository
         */
        $teamRepository = $this->em->getRepository(Team::class);

        // find total count
        return $teamRepository->findCountByCriteria($conditions);
    }

    /**
     * @param Request $request
     * @return Team
     * @throws ApiException
     */
    public function create(Request $request)
    {
        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        // find league
        $league = $leagueRepository->find($request->get('league_id'));
        if (empty($league)) {
            throw new ApiException(ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND, Response::HTTP_BAD_REQUEST);
        }

        // create team
        $team = new Team();
        $team->setName($request->get('name'));
        $team->setStrip($request->get('strip'));
        $team->setLeague($league);

        try {
            $this->em->persist($team);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new ApiException(ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME, Response::HTTP_BAD_REQUEST);
        }

        return $team;
    }

    /**
     * @param $id
     * @param Request $request
     * @return Team|null
     * @throws ApiException
     */
    public function update($id, Request $request)
    {
        /**
         * @var $teamRepository TeamRepository
         */
        $teamRepository = $this->em->getRepository(Team::class);

        $teamEntity = $teamRepository->find($id);

        if (empty($teamEntity)) {
            throw new ApiException(ApiCodes::ERR_CODE_TEAM_NOT_FOUND, Response::HTTP_BAD_REQUEST);
        }

        $changed = false;
        if (!empty($request->get('name'))) {
            $teamEntity->setName($request->get('name'));
            $changed = true;
        }
        if (!empty($request->get('strip'))) {
            $teamEntity->setStrip($request->get('strip'));
            $changed = true;
        }
        if (!empty($request->get('league_id'))) {
            /**
             * @var $leagueRepository LeagueRepository
             */
            $leagueRepository = $this->em->getRepository(League::class);

            // find league
            $league = $leagueRepository->find($request->get('league_id'));
            if (empty($league)) {
                throw new ApiException(ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND, Response::HTTP_BAD_REQUEST);
            }

            $teamEntity->setLeague($league);
            $changed = true;
        }

        // if need to change fields
        if (!empty($changed)) {

            try {
                $this->em->persist($teamEntity);
                $this->em->flush();
            } catch (UniqueConstraintViolationException $e) {
                throw new ApiException(ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME, Response::HTTP_BAD_REQUEST);
            }

            return $teamEntity;
        }

        throw new ApiException(ApiCodes::ERR_CODE_TEAM_NOT_MODIFIED, Response::HTTP_BAD_REQUEST);
    }

}
