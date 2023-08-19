<?php

namespace Ikechukwukalu\Clamavfileupload;

use Illuminate\Support\ServiceProvider;

class ClamavFileUploadServiceProvider extends ServiceProvider
{

    public const CONFIG = __DIR__.'/config/clamavfileupload.php';
    public const LANG = __DIR__.'/lang';
    public const DB = __DIR__.'/migrations';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(static::DB);
        $this->loadTranslationsFrom(static::LANG, 'clamavfileupload');

        $this->publishes([
            static::CONFIG => config_path('clamavfileupload.php'),
        ], 'cfu-config');
        $this->publishes([
            static::DB => database_path('migrations'),
        ], 'cfu-migrations');
        $this->publishes([
            static::LANG => lang_path('vendor/clamavfileupload'),
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
            static::CONFIG, 'clamavfileupload'
        );

        $this->app->register(EventServiceProvider::class);
        $this->app->register(FacadeServiceProvider::class);
    }
}
