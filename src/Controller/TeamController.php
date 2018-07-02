<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Exception\ApiException;
use App\Service\ApiCodes;
use App\Service\Validation\TeamValidation;

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
     * @Route("/v1/teams", name="v1_team_browse")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function browseAction(Request $request)
    {
        // get limit, offset request params
        $this->getRequestParams($request);

        /**
         * @var $teamService \App\Service\TeamService
         */
        $teamService = $this->get('team_service');

        // find data
        $teamEntities = $teamService->getPaginationList($request);

        // find total count
        $totalCount = $teamService->getPaginationCount($request);

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
     * @Route("/v1/teams", name="v1_team_create")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(Request $request)
    {
        /**
         * @var $teamService \App\Service\TeamService
         * @var $validation TeamValidation
         */
        $teamService = $this->get('team_service');
        $validation = $this->get('validation.team_service');

        // validate
        $this->errorFields = $validation->validateCreate($request);
        if (!empty($this->errorFields)) {
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        try {
            $team = $teamService->create($request);
        } catch(ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
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
     * @Route("/v1/teams/{id}", name="v1_team_update")
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

        try {
            $team = $teamService->update($id, $request);
        } catch(ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
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
