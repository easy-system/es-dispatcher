<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Dispatcher\Test;

use Es\Dispatcher\DispatchEvent;

class DispatchEventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once 'FakeController.php';
    }

    public function testConstructorOnSuccess()
    {
        $controller = new FakeController();
        $params     = [
            'foo' => 'bar',
        ];
        $event = new DispatchEvent($controller, 'FakeController', 'fake', $params);
        $this->assertSame($controller, $event->getContext());
        $this->assertSame('FakeController', $event->getParam('controller'));
        $this->assertSame('fake', $event->getParam('action'));
        $this->assertSame('bar', $event->getParam('foo'));
        $this->assertSame('FakeController@fake', $event->getName());
    }

    public function invalidControllerDataProvider()
    {
        $controllers = [
            true,
            false,
            100,
            'string',
            [],
        ];
        $return = [];
        foreach ($controllers as $controller) {
            $return[] = [$controller];
        }

        return $return;
    }

    /**
     * @dataProvider invalidControllerDataProvider
     */
    public function testConstructorRaiseExceptionIfInvalidControllerProvided($controller)
    {
        $this->setExpectedException('InvalidArgumentException');
        $event = new DispatchEvent($controller, 'FakeController', 'fake');
    }

    public function invalidControllerNameDataProvider()
    {
        $names = [
            true,
            false,
            100,
            '',
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($names as $name) {
            $return[] = [$name];
        }

        return $return;
    }

    /**
     * @dataProvider invalidControllerNameDataProvider
     */
    public function testConstructorRaiseExceptionIfInvalidControllerNameProvided($controllerName)
    {
        $this->setExpectedException('InvalidArgumentException');
        $event = new DispatchEvent(new FakeController(), $controllerName, 'fake');
    }

    public function invalidActionNameDataProvider()
    {
        $names = [
            true,
            false,
            100,
            '',
            'non-existent-action',
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($names as $name) {
            $return[] = [$name];
        }

        return $return;
    }

    /**
     * @dataProvider invalidActionNameDataProvider
     */
    public function testConstructorRaiseExceptionIfInvalidActionNameProvided($actionName)
    {
        $this->setExpectedException('InvalidArgumentException');
        $event = new DispatchEvent(new FakeController(), 'FakeController', $actionName);
    }
}
