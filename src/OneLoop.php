<?php

namespace Hassan\OneLoop;

use Illuminate\Support\Collection;

class OneLoop
{
    private $items;

    private $applicableMethods = [];

    private $limit = null;

    protected static $availableMethods = ['map', 'reject', 'filter', 'pluck', 'unique', 'groupBy'];

    public function __construct($items)
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Filter items using a callback
     *
     * @param callable $callback
     * @return $this
     */
    public function filter(callable $callback)
    {
        $this->applicableMethods['filter'] = $callback;
        return $this;
    }

    /**
     * Reject items using a callback (opposite of filter)
     *
     * @param callable $callback
     * @return $this
     */
    public function reject(callable $callback)
    {
        $this->applicableMethods['reject'] = $callback;
        return $this;
    }

    /**
     * Transform items using a callback
     *
     * @param callable $callback
     * @return $this
     */
    public function map(callable $callback)
    {
        $this->applicableMethods['map'] = $callback;
        return $this;
    }

    /**
     * Extract a single column/property from items
     *
     * @param string|callable $value
     * @param string|null $key
     * @return $this
     */
    public function pluck($value, $key = null)
    {
        $this->applicableMethods['pluck'] = [
            'value' => $value,
            'key' => $key
        ];
        return $this;
    }

    /**
     * Remove duplicate items
     *
     * @param string|callable|null $key
     * @return $this
     */
    public function unique($key = null)
    {
        $this->applicableMethods['unique'] = ['key' => $key];
        return $this;
    }

    /**
     * Group items by a key or callback result
     *
     * @param string|callable $groupBy
     * @return $this
     */
    public function groupBy($groupBy)
    {
        $this->applicableMethods['groupBy'] = ['groupBy' => $groupBy];
        return $this;
    }

    /**
     * Limit the number of results (enables early exit optimization)
     *
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Take the first N items (alias for limit)
     *
     * @param int $count
     * @return $this
     */
    public function take(int $count)
    {
        return $this->limit($count);
    }

    /**
     * Conditionally apply operations
     *
     * @param bool $condition
     * @param callable $callback
     * @param callable|null $default
     * @return $this
     */
    public function when($condition, callable $callback, callable $default = null)
    {
        if ($condition) {
            $callback($this);
        } elseif ($default) {
            $default($this);
        }
        return $this;
    }

    /**
     * Apply all queued operations in a single loop
     *
     * @return array
     */
    public function apply(): array
    {
        // Handle groupBy separately as it requires different result structure
        if (isset($this->applicableMethods['groupBy'])) {
            return $this->applyWithGroupBy();
        }

        $result = [];
        $seen = [];
        $count = 0;

        foreach ($this->items as $key => $item) {
            // Early exit if limit is reached
            if ($this->limit !== null && $count >= $this->limit) {
                break;
            }

            $currentItem = $item;
            $shouldInclude = true;

            // Apply all operations in sequence
            foreach ($this->applicableMethods as $method => $callback) {
                if (!$shouldInclude) {
                    break;
                }

                switch ($method) {
                    case 'filter':
                        if (!$callback($currentItem, $key)) {
                            $shouldInclude = false;
                        }
                        break;

                    case 'reject':
                        if ($callback($currentItem, $key)) {
                            $shouldInclude = false;
                        }
                        break;

                    case 'map':
                        $currentItem = $callback($currentItem, $key);
                        break;

                    case 'pluck':
                        $currentItem = $this->pluckValue($currentItem, $callback['value']);
                        break;

                    case 'unique':
                        $uniqueValue = $callback['key']
                            ? $this->getValueByKey($currentItem, $callback['key'])
                            : $currentItem;

                        $uniqueHash = is_object($uniqueValue) || is_array($uniqueValue)
                            ? serialize($uniqueValue)
                            : $uniqueValue;

                        if (isset($seen[$uniqueHash])) {
                            $shouldInclude = false;
                        } else {
                            $seen[$uniqueHash] = true;
                        }
                        break;
                }
            }

            if ($shouldInclude) {
                $result[] = $currentItem;
                $count++;
            }
        }

        return $result;
    }

    /**
     * Apply operations with groupBy
     *
     * @return array
     */
    private function applyWithGroupBy(): array
    {
        $result = [];
        $groupByConfig = $this->applicableMethods['groupBy'];

        foreach ($this->items as $key => $item) {
            $currentItem = $item;
            $shouldInclude = true;

            // Apply all operations except groupBy
            foreach ($this->applicableMethods as $method => $callback) {
                if ($method === 'groupBy') {
                    continue;
                }

                if (!$shouldInclude) {
                    break;
                }

                switch ($method) {
                    case 'filter':
                        if (!$callback($currentItem, $key)) {
                            $shouldInclude = false;
                        }
                        break;

                    case 'reject':
                        if ($callback($currentItem, $key)) {
                            $shouldInclude = false;
                        }
                        break;

                    case 'map':
                        $currentItem = $callback($currentItem, $key);
                        break;

                    case 'pluck':
                        $currentItem = $this->pluckValue($currentItem, $callback['value']);
                        break;
                }
            }

            if ($shouldInclude) {
                // Apply groupBy
                $groupKey = is_callable($groupByConfig['groupBy'])
                    ? $groupByConfig['groupBy']($currentItem, $key)
                    : $this->getValueByKey($currentItem, $groupByConfig['groupBy']);

                if (!isset($result[$groupKey])) {
                    $result[$groupKey] = [];
                }
                $result[$groupKey][] = $currentItem;
            }
        }

        return $result;
    }

    /**
     * Pluck a value from an item
     *
     * @param mixed $item
     * @param string|callable $value
     * @return mixed
     */
    private function pluckValue($item, $value)
    {
        if (is_callable($value)) {
            return $value($item);
        }

        return $this->getValueByKey($item, $value);
    }

    /**
     * Get value by key from object or array
     *
     * @param mixed $item
     * @param string $key
     * @return mixed
     */
    private function getValueByKey($item, $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        if (is_object($item)) {
            return $item->{$key} ?? null;
        }

        return null;
    }

    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof Collection) {
            return $items->all();
        }

        return (array) $items;
    }

    public function __call($name, $arguments)
    {
        if (
            isset($arguments[0]) &&
            in_array($name, static::$availableMethods, true) &&
            is_callable($arguments[0])
        ) {
            $this->applicableMethods[$name] = $arguments[0];

            return $this;
        }

        return $this->$name(...$arguments);
    }
}