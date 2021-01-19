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
    return view('welcome');
});

Route::any('/home/{page}', [App\Http\Controllers\HomeController::class, 'page'])->name('home');

Auth::routes(['register' => false]);



Route::group(['middleware' => ['auth']], function() {

    Route::group(['middleware' => ['admin']], function() {

        Route::any('admin/{page}', [App\Http\Controllers\AdminController::class, 'page'])->name('admin');
    });

    Route::group(['middleware' => ['not.admin']], function() {

        Route::any('/blueteam/{page}', [App\Http\Controllers\BlueTeamController::class, 'page'])->name('blueteam');

        Route::any('/asset', [App\Http\Controllers\AssetController::class, 'useAction'])->name('asset');

        Route::any('/redteam/{page}', [App\Http\Controllers\RedTeamController::class, 'page'])->name('redteam');
    
        Route::any('/attack/{page}', [App\Http\Controllers\AttackController::class, 'page'])->name('attack');
    
        Route::any('/learn/{page}', [App\Http\Controllers\LearnController::class, 'page'])->name('learn');
    });
});

//TestFill Assets
Route::any('/asset/prefillTest', [App\Http\Controllers\AssetController::class, 'prefillTest'])->name('prefillAssets');

Route::any('/setup', [App\Http\Controllers\SetupController::class, 'page'])->name('setup');
