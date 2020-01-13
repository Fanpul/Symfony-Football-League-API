<?php

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    /**
     * Success response structure for api
     *
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    public function responseJson($data = [], $status = Response::HTTP_OK)
    {
        return new JsonResponse([
            'code' => $status,
            'data' => $data
        ], $status);
    }
}
