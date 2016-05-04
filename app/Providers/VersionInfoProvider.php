<?php

namespace Castle\Providers;

use Cache;
use Illuminate\Support\ServiceProvider;

class VersionInfoProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        if (!Cache::has('castle.revision')) {
            Cache::put('castle.revision', exec('git rev-parse --short HEAD'), 360);
        }
    }
}
