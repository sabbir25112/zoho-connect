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

Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Route::get('/', 'Controller@start')->name('welcome');

    Route::get('/zoho-auth-init', 'ZohoAuthController@init')->name('zoho-auth-init');
    Route::get('/zoho-auth-callback', 'ZohoAuthController@callback');

    Route::group(['middleware' => 'check.access.token'], function () {
        Route::get('sync-projects', 'SyncController@syncProjects')->name('sync-projects');
        Route::get('sync-users', 'SyncController@syncUsers')->name('sync-users');
        Route::get('sync-tasklists', 'SyncController@syncTaskLists')->name('sync-tasklists');
        Route::get('sync-tasks', 'SyncController@syncTasks')->name('sync-tasks');
        Route::get('sync-sub-tasks', 'SyncController@syncSubTasks')->name('sync-sub-tasks');
        Route::post('sync-timesheets', 'SyncController@syncTimeSheet')->name('sync-timesheet');
    });
});


//Route::get('/', function () {
//    return view('welcome');
//});
