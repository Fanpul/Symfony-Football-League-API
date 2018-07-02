<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\Service\ApiCodes;
use App\Exception\ApiException;
use App\Controller\Traits\ApiResponseTrait;

class LeagueController extends Controller
{
    use ApiResponseTrait;

    /**
     * @Route("/v1/leagues", name="v1_league_browse")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function browseAction(Request $request)
    {
        // params with limit, offset
        $this->getRequestParams($request);

        /**
         * @var $leagueService \App\Service\LeagueService
         */
        $leagueService = $this->get('league_service');

        // find data
        $leagueEntities = $leagueService->getPaginationList($request);

        // find total count
        $totalCount = $leagueService->getPaginationCount();

        if (!empty($leagueEntities)) {

            // normalize entity
            $data = $this->normalize($leagueEntities, [
                'groups' => ['users']
            ]);

            return $this->responseJson( [
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
     * @Route("/v1/leagues/{id}", name="v1_league_delete")
     * @Method({"DELETE"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction($id, Request $request)
    {
        /**
         * @var $leagueService \App\Service\LeagueService
         */
        $leagueService = $this->get('league_service');

        try {
            $leagueService->delete($id);
        } catch(ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
        }

        return $this->responseJson( [
            'message' => 'League was successfully deleted'
        ]);
    }
}
