<?php

namespace App\Providers;

use App\Models\FrontPage;
use App\Models\Ads;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\PosterEmailVisibility::class);
        $this->app->scoped('native.form', fn()=>new \App\Support\NativeForm());
        \Illuminate\Foundation\AliasLoader::getInstance()->alias('Form', \App\Support\FormFacade::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('shared.pagination');
        Paginator::defaultSimpleView('shared.pagination');

        // Artisan, Composer and migration commands must bootstrap without an
        // existing database connection or an initialized application schema.
        if ($this->app->runningInConsole()) {
            return;
        }

        if (Schema::hasTable('application_settings')) {
            $user = Auth::user();
            $pages = FrontPage::all();
            $setting = gs();
            $arr = Ads::find(1);

            View::share([
                'user' => $user,
                'pages' => $pages->isEmpty() ? [] : $pages,
                'setting' => $setting,
                'arr' => $arr,
            ]);
        }
    }
}
