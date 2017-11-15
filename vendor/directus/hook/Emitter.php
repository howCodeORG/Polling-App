<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2017 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\Hook;

/**
 * Emitter
 *
 * Commands can be added in order to take action when a event happens.
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
class Emitter
{
    /**
     * High priority.
     *
     * @const int
     */
    const P_HIGH = 100;

    /**
     * Normal priority.
     *
     * @const int
     */
    const P_NORMAL = 0;

    /**
     * Low priority.
     *
     * @const int
     */
    const P_LOW = -100;

    /**
     * Action Listener type
     *
     * @const int
     */
    const TYPE_ACTION = 0;

    /**
     * Filter Listener type
     *
     * @const int
     */
    const TYPE_FILTER = 1;

    /**
     * List of registered action listeners
     *
     * @var array
     */
    protected $actionListeners = [];

    /**
     * List of registered filter listeners
     *
     * @var array
     */
    protected $filterListeners = [];

    /**
     * Add an action listener with the given name
     *
     * @param $name
     * @param $listener
     * @param int $priority
     */
    public function addAction($name, $listener, $priority = self::P_NORMAL)
    {
        $this->addListener($name, $listener, $priority, self::TYPE_ACTION);
    }

    /**
     * Add a filter listener wit the given name
     *
     * @param $name
     * @param $listener
     * @param int $priority
     */
    public function addFilter($name, $listener, $priority = self::P_NORMAL)
    {
        $this->addListener($name, $listener, $priority, self::TYPE_FILTER);
    }

    /**
     * Execute all the the actions listeners registered in the given name
     *
     * An Action execute the given listener and do not return any value.
     *
     * @param $name
     * @param null $data
     */
    public function run($name, $data = null)
    {
        $listeners = $this->getActionListeners($name);

        $this->executeListeners($listeners, $data, self::TYPE_ACTION);
    }

    /**
     * @see Emitter->run();
     *
     * @param $name
     * @param null $data
     */
    public function execute($name, $data = null)
    {
        $this->run($name, $data);
    }

    /**
     * Execute all the the filters listeners registered in the given name
     *
     * A Filter execute the given listener and return a modified given value
     *
     * @param $name
     * @param null $data
     *
     * @return mixed
     */
    public function apply($name, $data = null)
    {
        $listeners = $this->getFilterListeners($name);

        if ($listeners) {
            $data = $this->executeListeners($listeners, $data, self::TYPE_FILTER);
        }

        return $data;
    }

    /**
     * Get all the actions listeners
     *
     * @param $name
     *
     * @return array
     */
    public function getActionListeners($name)
    {
        return $this->getListeners($this->actionListeners, $name);
    }

    /**
     * Whether the hook action name given has listener or not
     *
     * @param $name
     *
     * @return bool
     */
    public function hasActionListeners($name)
    {
        return $this->getActionListeners($name) ? true : false;
    }

    /**
     * Get all the filters listeners
     *
     * @param $name
     *
     * @return array
     */
    public function getFilterListeners($name)
    {
        return $this->getListeners($this->filterListeners, $name);
    }

    /**
     * Whether the hook filter name given has listener or not
     *
     * @param $name
     *
     * @return bool
     */
    public function hasFilterListeners($name)
    {
        return $this->getFilterListeners($name) ? true : false;
    }

    /**
     * Add a listener
     *
     * @param $name
     * @param $listener
     * @param int $priority
     * @param int $type
     */
    protected function addListener($name, $listener, $priority = self::P_NORMAL, $type = self::TYPE_ACTION)
    {
        if (is_string($listener) && class_exists($listener)) {
            $listener = new $listener();
        }

        $this->validateListener($listener);

        if ($type == self::TYPE_FILTER) {
            $this->filterListeners[$name][$priority][] = $listener;
        } else {
            $this->actionListeners[$name][$priority][] = $listener;
        }
    }

    /**
     * Validate a listener
     *
     * @param $listener
     */
    protected function validateListener($listener)
    {
        if (!is_callable($listener) && !($listener instanceof HookInterface)) {
            throw new \InvalidArgumentException('Listener needs to be a callable or an instance of \Directus\Hook\HookInterface');
        }
    }

    /**
     * Get all listeners registered into a given name
     *
     * @param array $items
     * @param $name
     *
     * @return array
     */
    protected function getListeners(array $items, $name)
    {
        $functions = [];
        if (array_key_exists($name, $items)) {
            $listeners = $items[$name];
            krsort($listeners);
            $functions = call_user_func_array('array_merge', $listeners);
        }

        return $functions;
    }

    /**
     * Execute a given listeners list
     *
     * @param array $listeners
     * @param null $data
     * @param int $listenerType
     *
     * @return array|mixed|null
     */
    protected function executeListeners(array $listeners, $data = null, $listenerType = self::TYPE_ACTION)
    {
        $isFilterType = ($listenerType == self::TYPE_FILTER);
        foreach ($listeners as $listener) {
            if ($listener instanceof HookInterface) {
                $listener = [$listener, 'handle'];
            }

            if (!is_array($data)) {
                $data = [$data];
            }

            $returnedValue = call_user_func_array($listener, $data);
            if ($isFilterType) {
                $data = $returnedValue;
            }
        }

        return ($isFilterType ? $data : null);
    }
}
