<?php

namespace App\Service;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\League;
use App\Repository\LeagueRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LeagueService
{
    use ApiResponseTrait;

    private $em = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param bool $returnCount
     * @return mixed
     */
    public function getPaginationList(Request $request, $returnCount = false)
    {
        $this->getRequestParams($request);

        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        if ($returnCount) {
            // find total count
            return $leagueRepository->findCount();
        }

        // find data
        return $leagueRepository->findBy([], null, $this->requestParams['limit'], $this->requestParams['offset']);
    }

    /**
     * @param $id
     * @return bool|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete($id)
    {
        $leagueRepository = $this->em->getRepository(League::class);

        // find data
        $leagueEntity = $leagueRepository->find($id);

        if (empty($leagueEntity)) {
            $this->error = ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND;
            return $this->responseJson([], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($leagueEntity);
        $this->em->flush();

        return true;
    }


}