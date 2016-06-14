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

use Es\Mvc\ControllersInterface;
use Es\Services\ServiceLocator;
use LogicException;

class FakeControllers extends ServiceLocator implements ControllersInterface
{
    public function merge(ControllersInterface $source = null)
    {
        throw new LogicException(sprintf('The "%s" is stub.', __METHOD__));
    }
}
