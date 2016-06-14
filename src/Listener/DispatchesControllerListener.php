<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Dispatcher\Listener;

use Es\Dispatcher\DispatchEvent;
use Es\Events\EventsInterface;
use Es\Http\ServerInterface;
use Es\Mvc\ControllersInterface;
use Es\Services\ServicesTrait;
use Es\System\SystemEvent;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Dispatches the system controllers.
 */
class DispatchesControllerListener
{
    use ServicesTrait;

    /**
     * The controllers.
     *
     * @var \Es\Mvc\ControllersInterface
     */
    protected $controllers;

    /**
     * The events.
     *
     * @var \Es\Events\EventsInterface
     */
    protected $events;

    /**
     * The server.
     *
     * @var \Es\Http\ServerInterface
     */
    protected $server;

    /**
     * Sets the controllers.
     *
     * @param \Es\Mvc\ControllersInterface $controllers The controllers
     */
    public function setControllers(ControllersInterface $controllers)
    {
        $this->controllers = $controllers;
    }

    /**
     * Gets the controllers.
     *
     * @return \Es\Mvc\ControllersInterface The controllers
     */
    public function getControllers()
    {
        if (! $this->controllers) {
            $services    = $this->getServices();
            $controllers = $services->get('Controllers');
            $this->setControllers($controllers);
        }

        return $this->controllers;
    }

    /**
     * Sets the events.
     *
     * @param \Es\Events\EventsInterface $events The events
     */
    public function setEvents(EventsInterface $events)
    {
        $this->events = $events;
    }

    /**
     * Gets the events.
     *
     * @return \Es\Events\EventsInterface The events
     */
    public function getEvents()
    {
        if (! $this->events) {
            $services = $this->getServices();
            $events   = $services->get('Events');
            $this->setEvents($events);
        }

        return $this->events;
    }

    /**
     * Sets the server.
     *
     * @param \Es\Http\ServerInterface $server The server
     */
    public function setServer(ServerInterface $server)
    {
        $this->server = $server;
    }

    /**
     * Gets the server.
     *
     * @return \Es\Http\ServerInterface The server
     */
    public function getServer()
    {
        if (! $this->server) {
            $services = $this->getServices();
            $server   = $services->get('Server');
            $this->setServer($server);
        }

        return $this->server;
    }

    /**
     * Triggers the DispatchEvent.
     *
     * @param \Es\System\SystemEvent $event The system event
     *
     * @throws \RuntimeException If the ServerRequest not contain the
     *                           "controller" attribute
     */
    public function onDispatch(SystemEvent $event)
    {
        $server  = $this->getServer();
        $request = $server->getRequest();

        $controllerName = $request->getAttribute('controller');
        if (! $controllerName) {
            throw new RuntimeException(
                'Unable to dispatch the system event, the server request not '
                . 'contains the "controller" attribute.'
            );
        }
        $actionName = $request->getAttribute('action', 'index');

        $events = $this->getEvents();

        $controllers = $this->getControllers();
        $controller  = $controllers->get($controllerName);
        $event->setContext($controller);

        $dispatchEvent = new DispatchEvent(
            $controller, $controllerName, $actionName, $event->getParams()
        );

        $events->trigger($dispatchEvent);
        $result = $dispatchEvent->getResult();
        $target = SystemEvent::DISPATCH;
        if ($result instanceof ResponseInterface) {
            $target = SystemEvent::FINISH;
        }
        $event->setResult($target, $result);
    }

    /**
     * Dispatch the controller.
     *
     * @param \Es\Dispatcher\DispatchEvent $event The event of dispatch
     */
    public function doDispatch(DispatchEvent $event)
    {
        $controller = $event->getContext();
        $action     = $event->getParam('action') . 'Action';

        $server   = $this->getServer();
        $request  = $server->getRequest()->withAddedAttributes($event->getParams());
        $response = $server->getResponse();

        $result = call_user_func_array([$controller, $action], [$request, $response]);
        $event->setResult($result);
    }
}
