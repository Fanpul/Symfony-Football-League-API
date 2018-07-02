<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Exception\ApiException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
    public function indexAction(Request $request)
    {
        // params with limit, offset
        $this->getRequestParams($request);

        /**
         * @var $leagueService \App\Service\LeagueService
         */
        $leagueService = $this->get('league_service');

        // find data
        try {
            [$leagueEntities, $totalCount] = $leagueService->getPaginationData($this->requestParams);

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
        } catch (ApiException $e) {
            $this->error = $e->getMessage();
            return $this->responseJson([], $e->getCode());
        }
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
