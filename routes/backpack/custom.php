<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

//Route::get('admin/dashboard', 'App\Http\Controllers\AdminController@dashboard');
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('lini', 'LiniCrudController');
    Route::crud('material', 'MaterialCrudController');
    Route::crud('cost', 'CostCrudController');
    Route::crud('routecost', 'RouteCostCrudController');
    Route::crud('routelini', 'RouteLiniCrudController');
}); // this should be the absolute last line of this file
