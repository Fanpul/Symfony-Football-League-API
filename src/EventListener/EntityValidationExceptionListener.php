<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\EntityValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\ConstraintViolationInterface;

class EntityValidationExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if (!$exception instanceof EntityValidationException) {
            return;
        }

        $errors = [];
        foreach ($exception->getViolationList() as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $errors[$violation->getPropertyPath()]['errors'][] = $violation->getMessage();
        }

        $message = [
            'message' => $exception->getMessage(),
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'errors' => $errors,
        ];

        $response = new JsonResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY);

        $event->setResponse($response);
    }
}
