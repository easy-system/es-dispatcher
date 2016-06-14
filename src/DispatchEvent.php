<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Dispatcher;

use Es\Events\AbstractEvent;
use InvalidArgumentException;

/**
 * Event of dispatching.
 */
final class DispatchEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param mixed  $controller     The instance of controller
     * @param string $controllerName Name of the controller that is
     *                               known Controllers
     * @param string $actionName     Name of the action without "Action"
     *                               postfix
     * @param array  $params         Optional; event parameters, most
     *                               often its parameters of route matching
     *
     * @throws \InvalidArgumentException
     *
     * - If the received controller is not object
     * - If the received controller name is not non-empty string
     * - If the received action name is not non-empty string
     * - If the received controller not contain the specified action
     */
    public function __construct($controller, $controllerName, $actionName, array $params = [])
    {
        if (! is_string($controllerName) || empty($controllerName)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid controller name provided; must be a  non-empty '
                . 'string, "%s" received.',
                is_object($controllerName) ? get_class($controllerName)
                                           : gettype($controllerName)
            ));
        }
        if (! is_string($actionName) || empty($actionName)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid action name provided; must be a  non-empty '
                . 'string, "%s" received.',
                is_object($actionName) ? get_class($actionName)
                                       : gettype($actionName)
            ));
        }
        if (! is_object($controller)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid controller provided; must be an object, "%s" received',
                gettype($controller)
            ));
        }
        if (! method_exists($controller, $actionName . 'Action')) {
            throw new InvalidArgumentException(sprintf(
                'The class "%s" of controller "%s" not contain the action "%s".',
                get_class($controller),
                $controllerName,
                $actionName . 'Action'
            ));
        }
        $this->context = $controller;
        $this->params  = array_merge(
            $params, ['controller' => $controllerName, 'action' => $actionName]
        );
        $this->name = $controllerName . '@' . $actionName;
    }
}
