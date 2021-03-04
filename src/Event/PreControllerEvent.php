<?php


namespace Pulsar\Core\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class kernelEvent
 * @package App\Event
 */
class PreControllerEvent implements StoppableEventInterface
{

    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $propagationStopped = false;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var callable|null
     */
    private $controller;

    /**
     * kernelEvent constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Dispatcher to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Event is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }


    /**
     * @return callable|null
     */
    public function getController(): ?callable
    {
        return $this->controller;
    }

    /**
     * @param callable|null $controller
     * @return PreControllerEvent
     */
    public function setController(callable $controller): PreControllerEvent
    {
        $this->controller = $controller;
        return $this;
    }
}
