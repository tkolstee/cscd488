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

Auth::routes();

Route::get('/home', [App\Http\Controllers\AssetController::class, 'prefillTest'])->name('home');

Route::any('/blueteam/{page}', [App\Http\Controllers\BlueTeamController::class, 'page'])->name('blueteam');

Route::any('/redteam/{page}', [App\Http\Controllers\RedTeamController::class, 'page'])->name('redteam');

//TestFill Assets
Route::any('/asset/prefillTest', [App\Http\Controllers\AssetController::class, 'prefillTest'])->name('prefillAssets');

Route::any('/admin/{page}', [App\Http\Controllers\AdminController::class, 'page'])->name('admin');

Route::any('/setup', [App\Http\Controllers\SetupController::class, 'page'])->name('setup');
