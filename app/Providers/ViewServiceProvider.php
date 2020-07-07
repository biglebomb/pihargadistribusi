<?php

namespace App\Providers;

use App\Models\Lini;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...
//        View::composer(
//            'dashboard', 'App\Http\View\Composers\DashboardComposer'
//        );

//        // Using Closure based composers...
        View::composer('dashboard', function ($view) {
            $view->with('linis', Lini::all());
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
