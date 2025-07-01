<?php

namespace TrAddressPtt;

use Illuminate\Support\ServiceProvider;

class TrAddressServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/database/seeders' => database_path('seeders'),
        ], 'seeders');

        $this->publishes([
            __DIR__.'/../config/traddressptt.php' => config_path('traddressptt.php'),
        ], 'traddressptt-config');
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \TrAddressPtt\Console\Commands\ImportTrAddressPtt::class,
                \TrAddressPtt\Console\Commands\PublishJsonData::class,
            ]);
        }
    }
} 