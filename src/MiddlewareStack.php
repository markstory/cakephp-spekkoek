<?php
namespace Spekkoek;

use Countable;

class MiddlewareStack implements Countable
{
    protected $stack = [];

    /**
     * Get the middleware object at the provided index.
     *
     * @param int $index The index to fetch.
     * @return callable|null Either the callable middleware or null
     *   if the index is undefined.
     */
    public function get($index)
    {
        if (isset($this->stack[$index])) {
            return $this->stack[$index];
        }
        return null;
    }

    /**
     * Append a middleware callable to the end of the stack.
     *
     * @param callable $callable The middleware callable to append.
     * @return $this
     */
    public function push(callable $callable)
    {
        $this->stack[] = $callable;
        return $this;
    }

    /**
     * Prepend a middleware callable to the start of the stack.
     *
     * @param callable $callable The middleware callable to prepend.
     * @return $this
     */
    public function prepend(callable $callable)
    {
        array_unshift($this->stack, $callable);
        return $this;
    }

    /**
     * Get the number of connected middleware layers.
     *
     * Implement the Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->stack);
    }
}
