<?php

namespace Ikechukwukalu\Clamavfileupload;

use Illuminate\Support\ServiceProvider;

class ClamavfileuploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadTranslationsFrom(__DIR__.'/lang', 'clamavfileupload');

        $this->publishes([
            __DIR__.'/config/clamavfileupload.php' => config_path('clamavfileupload.php'),
        ], 'cfu-config');
        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'cfu-migrations');
        $this->publishes([
            __DIR__.'/lang' => lang_path('vendor/clamavfileupload'),
        ], 'cfu-lang');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/clamavfileupload.php', 'clamavfileupload'
        );

        $this->app->register(EventServiceProvider::class);
    }
}
