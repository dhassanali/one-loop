<?php

namespace Hassan\OneLoop;

use Illuminate\Support\Collection;

class OneLoop
{
    private $items;

    private $applicableMethods = [];

    protected static $availableMethods = ['map', 'reject'];

    public function __construct($items)
    {
        $this->items = $this->getArrayableItems($items);
    }

    public function apply(): array
    {
        foreach ($this->items as $key => $item) {
            $this->applyMethods($item, $key);
        }

        return $this->items;
    }

    private function applyMethods($item, $index): void
    {
        foreach ($this->applicableMethods as $key => $method) {
            if (! isset($this->items[$index])) {
                continue;
            }

            switch ($key) {
                case 'map':
                    $this->items[$index] = $method($item, $index);
                    break;

                case 'reject':
                    if ((bool) $method($item, $index)) {
                        unset($this->items[$index]);
                    }
                    break;
            }
        }
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
