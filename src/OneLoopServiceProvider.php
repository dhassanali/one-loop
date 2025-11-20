<?php

namespace Hassan\OneLoop;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class OneLoopServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register Collection macro
        Collection::macro('oneLoop', function () {
            return one_loop($this->all());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}