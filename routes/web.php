<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/linis', function(){
    return \App\Models\Lini::all();
});

Route::get('/provinces', 'ProvinceController@all');
Route::get('/province/{id}/regency', 'RegencyController@getByProvince');
Route::get('/regency/{id}', 'RegencyController@get');

Route::get('/materialgroup', 'MaterialGroupController@get');

Route::get('/routecost/{id}', 'RouteCostController@get');
Route::get('/routecost/{id}/{cost_id}', 'RouteCostController@getByCostType');

Route::get('/findroute/{from}/{to}', 'Admin\RouteLiniCrudController@findRoute');
Route::get('/findroutebylini/{to}', 'Admin\RouteLiniCrudController@findRouteByLini');
Route::get('/findroutebyregency/{to}/{group}', 'Admin\RouteLiniCrudController@findRouteByRegency');
