<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Manager\TeamManager;
use App\Model\Input\TeamInput;
use App\Model\Input\TeamsSearch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends Controller
{
    use ApiResponseTrait;

    private $manager;

    public function __construct(TeamManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/v1/teams", name="v1_team_browse", methods={"GET"})
     *
     * @param TeamsSearch $search
     * @return JsonResponse
     */
    public function indexAction(TeamsSearch $search): JsonResponse
    {
        return $this->json($this->manager->findAll($search));
    }

    /**
     * @Route("/v1/teams/{id}", name="v1_team_show", methods={"GET"})
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        return $this->responseJson($this->manager->find($id));
    }

    /**
     * @Route("/v1/teams", name="v1_team_create", methods={"POST"})
     * @ParamConverter(name="input", options={"validate": true})
     *
     * @param TeamInput $input
     * @return JsonResponse
     */
    public function createAction(TeamInput $input): JsonResponse
    {
        return $this->responseJson($this->manager->create($input), JsonResponse::HTTP_CREATED);
    }

    /**
     * @Route("/v1/teams/{id}", name="v1_team_update", methods={"PUT"})
     * @ParamConverter(name="input", options={"validate": true})
     *
     * @param $id
     * @param TeamInput $input
     * @return JsonResponse
     */
    public function updateAction(int $id, TeamInput $input): JsonResponse
    {
        return $this->responseJson($this->manager->update($id, $input));
    }
}
