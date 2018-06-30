<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\League;
use App\Entity\Team;
use App\Repository\LeagueRepository;
use App\Service\ApiCodes;
use App\Repository\TeamRepository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TeamController extends Controller
{
    use ApiResponseTrait {
        getRequestParams as _getRequestParams;
    }

    /**
     * @Route("/v1/team", name="v1_team_browse")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function browse(Request $request)
    {
        $this->getRequestParams($request);

        $conditions = [];
        if (!empty($this->requestParams['league_id'])) {
            $conditions['league_id'] = $this->requestParams['league_id'];
        }

        /**
         * @var $teamRepository TeamRepository
         */
        $em = $this->getDoctrine()->getManager();
        $teamRepository = $em->getRepository(Team::class);

        // find data
        $teamEntities = $teamRepository->findByCriteria($conditions, $this->requestParams['limit'], $this->requestParams['offset']);

        // find total count
        $totalCount = $teamRepository->findCountByCriteria($conditions);

        if (!empty($teamEntities)) {

            // normalize entity
            $data = $this->normalize($teamEntities, [
                'groups' => ['users']
            ]);

            return $this->responseJson([
                'total_count' => intval($totalCount),
                'count' => count($data),
                'limit' => $this->requestParams['limit'],
                'offset' => $this->requestParams['offset'],
                'data' => $data
            ]);
        }

        $this->error = ApiCodes::ERR_DATA_NOT_FOUND;
        return $this->responseJson([], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/v1/team", name="v1_team_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create(Request $request)
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

        /**
         * @var $leagueRepository LeagueRepository
         */
        $em = $this->getDoctrine()->getManager();
        $leagueRepository = $em->getRepository(League::class);

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
            $em->persist($team);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->error = ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        // normalize entity
        $data = $this->normalize($team, [
            'groups' => ['users']
        ]);

        return $this->responseJson([
            'message' => 'Team was successfully created',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/v1/team/{id}", name="v1_team_update")
     * @Method({"PUT"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function update($id, Request $request)
    {
        $this->getRequestParams($request);

        $em = $this->getDoctrine()->getManager();
        /**
         * @var $teamRepository TeamRepository
         */
        $teamRepository = $em->getRepository(Team::class);

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
            $leagueRepository = $em->getRepository(League::class);

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
                $em->persist($teamEntity);
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $this->error = ApiCodes::ERR_CODE_TEAM_DUPLICATE_NAME;
                return $this->responseJson([], Response::HTTP_BAD_REQUEST);
            }

            // normalize entity
            $data = $this->normalize($teamEntity, [
                'groups' => ['users']
            ]);

            return $this->responseJson([
                'message' => 'Team was successfully updated',
                'data' => $data
            ]);
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
