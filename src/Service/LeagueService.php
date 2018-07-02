<?php

namespace App\Service;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\League;
use App\Exception\ApiException;
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
     * @return mixed
     */
    public function getPaginationList(Request $request)
    {
        $this->getRequestParams($request);

        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        // find data
        return $leagueRepository->findBy([], null, $this->requestParams['limit'], $this->requestParams['offset']);
    }

    /**
     * @return mixed
     */
    public function getPaginationCount()
    {
        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        // find total count
        return $leagueRepository->findCount();
    }

    /**
     * @param $id
     * @return bool
     * @throws ApiException
     */
    public function delete($id)
    {
        $leagueRepository = $this->em->getRepository(League::class);

        // find data
        $leagueEntity = $leagueRepository->find($id);
        if (empty($leagueEntity)) {
            throw new ApiException(ApiCodes::ERR_CODE_LEAGUE_NOT_FOUND, Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->em->remove($leagueEntity);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new ApiException(ApiCodes::ERR_INTERNAL_SERVER_ERROR, Response::HTTP_INTERNAL_SERVER_ERROR);
        };

        return true;
    }

}
