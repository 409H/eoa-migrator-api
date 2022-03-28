<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if(file_exists(storage_path('network_mapping.json'))) {
            $mapping = json_decode(file_get_contents(storage_path('network_mapping.json')), true);
            config(['network_mapping' => $mapping]);
        }
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
