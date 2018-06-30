<?php

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;

use App\Service\ApiCodes;

trait ApiResponseTrait
{
    public $error = null;
    public $errorFields = [];

    public $limit = 10;
    public $maxLimit = 100;
    public $offset = 0;

    protected $requestParams = [];

    /**
     * Main response function for api
     *
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    public function responseJson($data = [], $status = Response::HTTP_OK)
    {
        $error = [];
        if (!empty($this->error)) {
            $data = [];
            $error = [
                'code' => $this->error,
                'message' => ApiCodes::getErrorMessageByCode($this->error),
            ];
        }

        if (!empty($this->errorFields)) {
            $data = [];
            $this->errorFields = array_map(function ($code) {
                return ApiCodes::getErrorMessageByCode($code);
            }, $this->errorFields);

            $error = [
                'fields' => $this->errorFields
            ];
        }

        return new JsonResponse([
            'status' => $status,
            'data' => $data,
            'error' => $error
        ], $status);
    }

    /**
     * Normalize entity to array
     * @param $data
     * @param array $context
     * @return array|bool|float|int|mixed|string
     */
    public function normalize($data, array $context = array())
    {
        $normalizer = new GetSetMethodNormalizer(new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader())));

        // Add Circular reference handler
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer([$normalizer]);

        return $serializer->normalize($data, null, $context);
    }

    /**
     * @param Request $request
     */
    protected function getRequestParams(Request $request)
    {
        $params = $this->getRequestData($request);

        $limit = !empty($params['limit']) ? intval($params['limit']) : $this->limit;

        if ($limit > $this->maxLimit) {
            $limit = $this->maxLimit;
        }
        if ($limit <= 0) {
            $limit = $this->limit;
        }
        $this->requestParams['limit'] = $limit;
        $this->requestParams['offset'] = !empty($params['offset']) ? intval($params['offset']) : $this->offset;
    }

    /**
     * get REQUEST params
     * @param Request $request
     * @return array
     */
    protected function getRequestData(Request $request)
    {
        $postParams = $request->request->all();
        $getParams = $request->query->all();

        if (empty($postParams)) {
            $postParams = $getParams;
        }
        return $postParams;
    }

}