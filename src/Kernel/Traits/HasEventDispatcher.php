<?php

namespace Nip\Http\Kernel\Traits;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Adds event dispatching capabilities to the kernel
 */
trait HasEventDispatcher
{
    /**
     * @var EventDispatcherInterface|null
     */
    protected $eventDispatcher;

    /**
     * Get the event dispatcher instance.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = $this->createEventDispatcher();
        }

        return $this->eventDispatcher;
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param EventDispatcherInterface $dispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;

        return $this;
    }

    /**
     * Create a new event dispatcher instance.
     *
     * This method can be overridden to use a custom event dispatcher
     * or to configure the dispatcher (e.g., add subscribers).
     *
     * @return EventDispatcherInterface
     */
    protected function createEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();

        // Register any boot subscribers
        $this->registerEventSubscribers($dispatcher);

        return $dispatcher;
    }

    /**
     * Register event subscribers.
     *
     * Override this method in your kernel to register custom event subscribers.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    protected function registerEventSubscribers(EventDispatcherInterface $dispatcher): void
    {
        // Override in child classes to register subscribers
    }

    /**
     * Dispatch an event to all registered listeners.
     *
     * @param Event $event The event to pass to the listeners
     * @param string|null $eventName The name of the event to dispatch
     * @return Event
     */
    protected function dispatchEvent(Event $event, ?string $eventName = null): Event
    {
        return $this->getEventDispatcher()->dispatch($event, $eventName);
    }
}
