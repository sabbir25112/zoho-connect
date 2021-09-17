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

Route::group(['namespace' => 'App\Http\Controllers', 'middleware' => 'auth'], function () {
    Route::get('/zoho-auth-init', 'ZohoAuthController@init')->name('zoho-auth-init');
    Route::get('/zoho-auth-callback', 'ZohoAuthController@callback');

    Route::group(['middleware' => 'check.access.token'], function () {
        Route::get('/', 'Controller@start')->name('welcome');
        Route::get('sync-projects', 'SyncController@syncProjects')->name('sync-projects');
        Route::get('sync-users', 'SyncController@syncUsers')->name('sync-users');
        Route::get('sync-tasklists', 'SyncController@syncTaskLists')->name('sync-tasklists');
        Route::get('sync-tasks', 'SyncController@syncTasks')->name('sync-tasks');
        Route::get('sync-sub-tasks', 'SyncController@syncSubTasks')->name('sync-sub-tasks');
        Route::get('sync-bugs', 'SyncController@syncBugs')->name('sync-bugs');
        Route::post('sync-timesheets', 'SyncController@syncTimeSheet')->name('sync-timesheet');
    });

    Route::get('add-user', 'ZohoConnectUserController@addUser')->name('add-user');
    Route::post('store-user', 'ZohoConnectUserController@storeUser')->name('store-user');
    Route::get('change-password', 'ZohoConnectUserController@changePassword')->name('change-password');
    Route::post('store-password', 'ZohoConnectUserController@storePassword')->name('store-password');
    Route::get('logout', 'Auth\LoginController@logout');
});

Auth::routes(['register' => false]);

Route::get('test-queue', function () {
    $project = \App\Models\Project::find(685798000013322790)->toArray();
    $userSyncer = new \App\Zoho\TaskSyncer($project);
    dd($userSyncer->call());
});
