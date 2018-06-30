<?php

namespace App\Controller;

use App\Repository\LeagueRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use App\Entity\League;
use App\Service\ApiCodes;
use App\Controller\Traits\ApiResponseTrait;

class LeagueController extends Controller
{
    use ApiResponseTrait;

    /**
     * @Route("/v1/league", name="v1_league_browse")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function browse(Request $request)
    {
        $this->getRequestParams($request);

        $em = $this->getDoctrine()->getManager();

        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $em->getRepository(League::class);

        // find data
        $leagueEntities = $leagueRepository->findBy([], null, $this->requestParams['limit'], $this->requestParams['offset']);

        // find total count
        $totalCount = $leagueRepository->findCount();

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
        } else {
            $this->error = ApiCodes::ERR_DATA_NOT_FOUND;
            return $this->responseJson([], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Route("/v1/league/{id}", name="v1_league_delete")
     * @Method({"DELETE"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete($id, Request $request)
    {
        /**
         * @var $leagueRepository LeagueRepository
         */
        $em = $this->getDoctrine()->getManager();
        $leagueRepository = $em->getRepository(League::class);

        // find data
        $leagueEntity = $leagueRepository->find($id);

        if (empty($leagueEntity)) {
            $this->error = ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $em->remove($leagueEntity);
        $em->flush();

        return $this->responseJson( [
            'message' => 'League was successfully deleted'
        ]);
    }
}
