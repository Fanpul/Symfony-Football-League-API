<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Exception\ApiException;
use App\Service\ApiCodes;
use App\Service\Validation\TeamValidation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function indexAction(Request $request)
    {
        // get limit, offset request params
        $this->getRequestParams($request);

        /**
         * @var $teamService \App\Service\TeamService
         */
        $teamService = $this->get('team_service');

        try {
            [$teamEntities, $totalCount] = $teamService->getPaginationData($this->requestParams);

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

        } catch (ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
        }
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

        // validate fields
        if (!$validation->validateCreate($request)) {
            $this->errorFields = $validation->getErrorFields();
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->getRequestParams($request);

            $team = $teamService->create($this->requestParams);

            // normalize entity
            $data = $this->normalize($team, [
                'groups' => ['users']
            ]);

            return $this->responseJson([
                'message' => 'Team was successfully created',
                'data' => $data
            ], Response::HTTP_CREATED);

        } catch (ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
        }
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

        $params = [
            'name' => $request->get('name'),
            'strip' => $request->get('strip'),
            'league_id' => $request->get('league_id'),
        ];

        try {
            $team = $teamService->update($id, $params);

            // normalize entity
            $data = $this->normalize($team, [
                'groups' => ['users']
            ]);

            return $this->responseJson([
                'message' => 'Team was successfully updated',
                'data' => $data
            ]);

        } catch (ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
        }
    }

    /**
     * get control request params
     * @param Request $request
     */
    protected function getRequestParams(Request $request)
    {
        $this->_getRequestParams($request);
        $params = $this->getRequestData($request);

        // additional params
        $this->requestParams['league_id'] = (int)$params['league_id'] ?? null;
        $this->requestParams['name'] = $params['name'] ?? null;
        $this->requestParams['strip'] = $params['strip'] ?? null;
    }
}
