<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\DBAL\Exception\ServerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class KernelExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        $message = [
            'message' => $this->convertDBALToGeneralError($exception),
            'code' => $exception->getCode(),
            'errors' => [],
        ];

        $response = new JsonResponse();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->add($exception->getHeaders());
            $message['code'] = $exception->getStatusCode();
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->setData($message);

        $event->setResponse($response);
    }

    private function convertDBALToGeneralError(\Exception $exception): string
    {
        if (!$exception instanceof ServerException) {
            return $exception->getMessage();
        }

        return 'Unable to process your request, our engineers will fix it shortly';
    }
}
