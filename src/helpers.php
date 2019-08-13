<?php

if (! function_exists('one_loop')) {
    /**
     * @param \Illuminate\Support\Collection|array $items
     * @return mixed
     */
    function one_loop($items)
    {
        return new \Hassan\OneLoop\OneLoop($items);
    }
}
