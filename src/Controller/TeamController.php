<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Service\ApiCodes;

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
    public function browseAction(Request $request)
    {
        $this->getRequestParams($request);

        /**
         * @var $teamService \App\Service\TeamService
         */
        $teamService = $this->get('team_service');

        // find data
        $teamEntities = $teamService->getPaginationList($request);

        // find total count
        $totalCount = $teamService->getPaginationList($request, true);

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
    public function createAction(Request $request)
    {
        /**
         * @var $teamService \App\Service\TeamService
         */
        $teamService = $this->get('team_service');

        $validate = $teamService->validateCreate($request);
        if ($validate instanceof Response) {
            return $validate;
        }

        $team = $teamService->create($request);
        if ($team instanceof Response) {
            return $team;
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
    public function updateAction($id, Request $request)
    {
        /**
         * @var $teamService \App\Service\TeamService
         */
        $teamService = $this->get('team_service');

        $team = $teamService->update($id, $request);
        if ($team instanceof Response) {
            return $team;
        }

        // normalize entity
        $data = $this->normalize($team, [
            'groups' => ['users']
        ]);

        return $this->responseJson([
            'message' => 'Team was successfully updated',
            'data' => $data
        ]);
    }
}
