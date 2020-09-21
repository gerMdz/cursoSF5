<?php

declare(strict_types=1);

namespace App\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use function get_class;
use function time;

class JsonExceptionResponseTransformerListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $data = [
                'class' => get_class($exception),
                'code' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ];

            $event->setResponse($this->prepareResponse($data, $data['code']));
        }
    }

    private function prepareResponse(array $data, int $statusCode): JsonResponse
    {
        $response = new JsonResponse($data, $statusCode);
        $response->headers->set('Server-Time', time());
        $response->headers->set('X-Error-Code', $statusCode);

        return $response;
    }
}
