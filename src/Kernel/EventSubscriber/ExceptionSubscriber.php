<?php

namespace Nip\Http\Kernel\EventSubscriber;

use Nip\Http\Kernel\Event\ExceptionEvent;
use Nip\Http\Kernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Example event subscriber for handling exceptions
 *
 * This subscriber demonstrates how to handle exceptions using Symfony-style events.
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to handle exceptions early
            KernelEvents::EXCEPTION => ['onKernelException', 100],
        ];
    }

    /**
     * Handles exceptions and creates appropriate responses.
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        // Get the exception
        $exception = $event->getThrowable();
        
        // Determine if the client expects JSON
        $request = $event->getRequest();
        $expectsJson = $request->headers->get('Accept') === 'application/json' 
            || $request->headers->get('Content-Type') === 'application/json';

        // Create appropriate response based on exception type
        if ($expectsJson) {
            $this->createJsonExceptionResponse($event, $exception);
        }
        
        // Note: If no response is set, the default exception handling will take over
    }

    /**
     * Creates a JSON response for exceptions.
     *
     * @param ExceptionEvent $event
     * @param \Throwable $exception
     */
    protected function createJsonExceptionResponse(ExceptionEvent $event, \Throwable $exception): void
    {
        $statusCode = $exception instanceof HttpExceptionInterface 
            ? $exception->getStatusCode() 
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $data = [
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ],
        ];

        // In development, you might want to include more details
        // if ($this->debug) {
        //     $data['error']['trace'] = $exception->getTraceAsString();
        // }

        $response = new JsonResponse($data, $statusCode);
        
        // Optionally set the response on the event
        // Uncomment to enable automatic JSON exception responses
        // $event->setResponse($response);
    }
}
