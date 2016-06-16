<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Dispatcher\Test\Listener;

use Es\Dispatcher\DispatchEvent;
use Es\Dispatcher\Listener\DispatchesControllerListener;
use Es\Dispatcher\Test\FakeController;
use Es\Dispatcher\Test\FakeControllers;
use Es\Events\Events;
use Es\Http\Response;
use Es\Http\ServerRequest;
use Es\Server\Server;
use Es\System\SystemEvent;

class DispatchesControlerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $testDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        require_once $testDir . 'FakeControllers.php';
        require_once $testDir . 'FakeController.php';
    }

    public function testOnDispatchRaiseExceptionIfRequestNotHaveTheControllerAttribute()
    {
        $server   = new Server();
        $request  = new ServerRequest();
        $response = new Response();
        $server->setRequest($request);
        $server->setResponse($response);

        $dispatcher = new DispatchesControllerListener();
        $dispatcher->setServer($server);

        $this->setExpectedException('RuntimeException');
        $dispatcher->onDispatch(new SystemEvent());
    }

    public function testOnDispatchIfResultIsInstanceOfResponse()
    {
        $server   = new Server();
        $request  = $this->getMock(ServerRequest::CLASS);
        $response = new Response();
        $server->setRequest($request);
        $server->setResponse($response);

        $controller  = new FakeController();
        $controllers = new FakeControllers();
        $controllers->set('FakeController', $controller);

        $events = $this->getMock(Events::CLASS);

        $dispatcher = new DispatchesControllerListener();
        $dispatcher->setServer($server);
        $dispatcher->setControllers($controllers);
        $dispatcher->setEvents($events);

        $request
            ->expects($this->at(0))
            ->method('getAttribute')
            ->with($this->identicalTo('controller'))
            ->will($this->returnValue('FakeController'));

        $request
            ->expects($this->at(1))
            ->method('getAttribute')
            ->with($this->identicalTo('action'), $this->identicalTo('index'))
            ->will($this->returnValue('fake'));

        $events
            ->expects($this->once())
            ->method('trigger')
            ->with($this->callback(function ($event) use ($controller) {
                if (! $event instanceof DispatchEvent) {
                    return false;
                }
                if ('FakeController' !== $event->getParam('controller')) {
                    return false;
                }
                if ('fake' !== $event->getParam('action')) {
                    return false;
                }
                if ($controller !== $event->getContext()) {
                    return false;
                }

                return true;
            }))
            ->will($this->returnCallback(function ($event) use ($response) {
                $event->setResult($response);
            }));

        $event = new SystemEvent();
        $dispatcher->onDispatch($event);
        $this->assertSame($response, $event->getResult(SystemEvent::FINISH));
    }

    public function testOnDispatchIfResultIsNotInstanceOfResponse()
    {
        $result = 'Lorem ipsum dolor sit amet';

        $server   = new Server();
        $request  = $this->getMock(ServerRequest::CLASS);
        $response = new Response();
        $server->setRequest($request);
        $server->setResponse($response);

        $controller  = new FakeController();
        $controllers = new FakeControllers();
        $controllers->set('FakeController', $controller);

        $events = $this->getMock(Events::CLASS);

        $dispatcher = new DispatchesControllerListener();
        $dispatcher->setServer($server);
        $dispatcher->setControllers($controllers);
        $dispatcher->setEvents($events);

        $request
            ->expects($this->at(0))
            ->method('getAttribute')
            ->with($this->identicalTo('controller'))
            ->will($this->returnValue('FakeController'));

        $request
            ->expects($this->at(1))
            ->method('getAttribute')
            ->with($this->identicalTo('action'), $this->identicalTo('index'))
            ->will($this->returnValue('fake'));

        $events
            ->expects($this->once())
            ->method('trigger')
            ->with($this->callback(function ($event) use ($controller) {
                if (! $event instanceof DispatchEvent) {
                    return false;
                }
                if ('FakeController' !== $event->getParam('controller')) {
                    return false;
                }
                if ('fake' !== $event->getParam('action')) {
                    return false;
                }
                if ($controller !== $event->getContext()) {
                    return false;
                }

                return true;
            }))
            ->will($this->returnCallback(function ($event) use ($result) {
                $event->setResult($result);
            }));

        $event = new SystemEvent();
        $dispatcher->onDispatch($event);
        $this->assertSame($result, $event->getResult(SystemEvent::DISPATCH));
    }

    public function testDoDispatch()
    {
        $result = 'Lorem ipsum dolor sit amet';

        $params = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $controller = $this->getMock('FakeController', ['fakeAction']);
        $event      = new DispatchEvent($controller, 'FakeController', 'fake', $params);

        $request  = $this->getMock(ServerRequest::CLASS);
        $response = new Response();
        $server   = new Server();
        $server->setRequest($request);
        $server->setResponse($response);

        $dispatcher = new DispatchesControllerListener();
        $dispatcher->setServer($server);

        $request
            ->expects($this->once())
            ->method('withAddedAttributes')
            ->with($this->callback(function ($attributes) use ($params) {
                return empty(array_diff($params, $attributes));
            }))
            ->will($this->returnSelf());

        $controller
            ->expects($this->once())
            ->method('fakeAction')
            ->with($this->identicalTo($request), $this->identicalTo($response))
            ->will($this->returnValue($result));

        $dispatcher->doDispatch($event);
    }
}
