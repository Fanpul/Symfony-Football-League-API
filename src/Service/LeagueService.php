<?php

namespace App\Service;

use App\Entity\League;
use App\Exception\ApiException;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class LeagueService
{
    private $em = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $params
     * @return array
     * @throws ApiException
     */
    public function getPaginationData($params)
    {
        /**
         * @var $leagueRepository LeagueRepository
         */
        $leagueRepository = $this->em->getRepository(League::class);

        // find data
        $leagueEntities = $leagueRepository->findBy([], null, $params['limit'], $params['offset']);

        if (empty($leagueEntities)) {
            throw new ApiException(ApiCodes::ERR_DATA_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        // find total count
        $totalCount = $leagueRepository->count([]);

        return [$leagueEntities, $totalCount];
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
