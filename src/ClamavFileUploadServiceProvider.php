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
        $this->loadMigrationsFrom(self::DB);
        $this->loadTranslationsFrom(self::LANG, 'clamavfileupload');

        $this->publishes([
            self::CONFIG => config_path('clamavfileupload.php'),
        ], 'cfu-config');
        $this->publishes([
            self::DB => database_path('migrations'),
        ], 'cfu-migrations');
        $this->publishes([
            self::LANG => lang_path('vendor/clamavfileupload'),
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
            self::CONFIG, 'clamavfileupload'
        );

        $this->app->register(EventServiceProvider::class);
    }
}
