<?php
// src/EventListener/ApiExceptionListener.php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Обрабатываем только API-запросы
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        [$status, $message, $errors] = match (true) {
            $exception instanceof \DomainException         => [
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception->getMessage(),
                null,
            ],
            $exception instanceof ValidationFailedException => [
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Ошибка валидации',
                $this->formatValidationErrors($exception),
            ],
            $exception instanceof HttpExceptionInterface   => [
                $exception->getStatusCode(),
                $exception->getMessage(),
                null,
            ],
            default => [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Внутренняя ошибка сервера',
                null,
            ],
        };

        $body = ['message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        $event->setResponse(new JsonResponse($body, $status));
    }

    private function formatValidationErrors(ValidationFailedException $e): array
    {
        $errors = [];
        foreach ($e->getViolations() as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }
        return $errors;
    }
}
