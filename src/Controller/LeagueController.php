<?php

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Manager\LeagueManager;
use App\Model\Input\LeaguesSearch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeagueController extends Controller
{
    use ApiResponseTrait;

    private $manager;

    public function __construct(LeagueManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/v1/leagues", name="v1_league_browse", methods={"GET"})
     *
     * @param LeaguesSearch $search
     * @return JsonResponse
     */
    public function indexAction(LeaguesSearch $search): JsonResponse
    {
        return $this->json($this->manager->findAll($search));
    }

    /**
     * @Route("/v1/leagues/{id}", name="v1_league_delete", methods={"DELETE"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function removeAction(int $id): Response
    {
        $this->manager->remove($id);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
