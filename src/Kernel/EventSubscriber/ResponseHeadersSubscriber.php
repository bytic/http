<?php

namespace Nip\Http\Kernel\EventSubscriber;

use Nip\Http\Kernel\Event\ResponseEvent;
use Nip\Http\Kernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Example event subscriber that adds custom headers to all responses
 *
 * This is an example of how to use Symfony-style event subscribers with the kernel.
 */
class ResponseHeadersSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    /**
     * Adds custom headers to the response.
     *
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        
        // Example: Add a custom header to identify responses processed by this kernel
        // Uncomment the line below to add the header
        // $response->headers->set('X-Powered-By', 'ByTIC HTTP Kernel');
    }
}
