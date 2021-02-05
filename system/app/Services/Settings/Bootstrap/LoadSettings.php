<?php

namespace App\Services\Settings\Bootstrap;

use App\Services\Settings\SettingsManager;
use Illuminate\Contracts\Foundation\Application;

class LoadSettings
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        foreach (config('app.settings') as $class) {
            SettingsManager::load($class);
        }
    }
}