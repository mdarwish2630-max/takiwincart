<?php

namespace Workdo\ProductVault\Providers;

use Illuminate\Support\ServiceProvider;
use Workdo\ProductVault\Providers\EventServiceProvider;

class ProductVaultServiceProvider extends ServiceProvider
{
    protected $moduleName = 'ProductVault';
    protected $moduleNameLower = 'productvault';

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'productvault');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->registerTranslations();
    }

    public function register()
    {
        $this->app->register(EventServiceProvider::class);
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
        }
    }
}