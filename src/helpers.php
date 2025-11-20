<?php

if (!function_exists('one_loop')) {
    /**
     * Create a new OneLoop instance
     *
     * @param array|iterable $data
     * @return \Hassan\OneLoop\OneLoop
     */
    function one_loop($data)
    {
        return new \Hassan\OneLoop\OneLoop($data);
    }
}