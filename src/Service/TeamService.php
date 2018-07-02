<?php

namespace App\Service;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\League;
use App\Entity\Team;
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
     * @return bool|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function validateCreate(Request $request)
    {
        $this->getRequestParams($request);

        // check required fields
        if (empty($this->requestParams['name'])) {
            $this->errorFields['name'] = ApiCodes::ERR_REQUIRED_PARAM;
        }
        if (empty($this->requestParams['strip'])) {
            $this->errorFields['strip'] = ApiCodes::ERR_REQUIRED_PARAM;
        }
        if (empty($this->requestParams['league_id'])) {
            $this->errorFields['league_id'] = ApiCodes::ERR_REQUIRED_PARAM;
        }

        if (!empty($this->errorFields)) {
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }
        return true;
    }

    /**
     * @param Request $request
     * @param bool $returnCount
     * @return mixed
     */
    public function getPaginationList(Request $request, $returnCount = false)
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

        if ($returnCount) {
            // find total count
            return $teamRepository->findCountByCriteria($conditions);
        }

        // find data
        return $teamRepository->findByCriteria($conditions, $this->requestParams['limit'], $this->requestParams['offset']);
    }

    /**
     * @param Request $request
     * @return Team|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request)
    {
        $this->getRequestParams($request);

        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        // find league
        $league = $leagueRepository->find($this->requestParams['league_id']);
        if (empty($league)) {
            $this->error = ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        // create team
        $team = new Team();
        $team->setName($this->requestParams['name']);
        $team->setStrip($this->requestParams['strip']);
        $team->setLeague($league);

        try {
            $this->em->persist($team);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->error = ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        return $team;
    }

    /**
     * @param $id
     * @param Request $request
     * @return Team|null|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update($id, Request $request)
    {
        $this->getRequestParams($request);

        /**
         * @var $teamRepository TeamRepository
         */
        $teamRepository = $this->em->getRepository(Team::class);

        $teamEntity = $teamRepository->find($id);

        if (empty($teamEntity)) {
            $this->error = ApiCodes::ERR_CODE_TEAM_NOT_FOUND;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $changed = false;
        if (!empty($this->requestParams['name'])) {
            $teamEntity->setName($this->requestParams['name']);
            $changed = true;
        }
        if (!empty($this->requestParams['strip'])) {
            $teamEntity->setStrip($this->requestParams['strip']);
            $changed = true;
        }
        if (!empty($this->requestParams['league_id'])) {
            /**
             * @var $leagueRepository LeagueRepository
             */
            $leagueRepository = $this->em->getRepository(League::class);

            // find league
            $league = $leagueRepository->find($this->requestParams['league_id']);
            if (empty($league)) {
                $this->error = ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND;
                return $this->responseJson([], Response::HTTP_BAD_REQUEST);
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
                $this->error = ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME;
                return $this->responseJson([], Response::HTTP_BAD_REQUEST);
            }

            return $teamEntity;
        }

        $this->error = ApiCodes::ERR_CODE_TEAM_NOT_MODIFIED;
        return $this->responseJson([], Response::HTTP_BAD_REQUEST);
    }

    /**
     * get control request params
     * @param Request $request
     */
    protected function getRequestParams(Request $request)
    {
        $this->_getRequestParams($request);
        $params = $this->getRequestData($request);

        // league_id
        $this->requestParams['league_id'] = null;
        if (!empty($params['league_id'])) {
            $this->requestParams['league_id'] = intval($params['league_id']);
        }

        // name
        $this->requestParams['name'] = null;
        if (!empty($params['name'])) {
            $this->requestParams['name'] = $params['name'];
        }

        // strip
        $this->requestParams['strip'] = null;
        if (!empty($params['strip'])) {
            $this->requestParams['strip'] = $params['strip'];
        }
    }

}
