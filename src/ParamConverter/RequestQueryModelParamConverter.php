<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Model\Request\RequestQueryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

final class RequestQueryModelParamConverter implements ParamConverterInterface
{
    /**
     * @var GetSetMethodNormalizer
     */
    private $normalizer;

    public function __construct(GetSetMethodNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            $query = $this->normalizer->denormalize(
                $request->query->all(),
                $configuration->getClass(),
                null,
                [
                    GetSetMethodNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ]
            );
        } catch (ExceptionInterface $exception) {
            throw new \LogicException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $request->attributes->set($configuration->getName(), $query);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        if (empty($configuration->getClass())) {
            return false;
        }

        return \in_array(RequestQueryInterface::class, class_implements($configuration->getClass()), true);
    }
}
