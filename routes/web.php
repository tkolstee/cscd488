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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/blueteam/index', [App\Http\Controllers\BlueTeamController::class, 'index'])->name('blueteam/index');
Route::any('/blueteam/create', [App\Http\Controllers\BlueTeamController::class, 'create'])->name('blueteam/create');
Route::any('/blueteam/join', [App\Http\Controllers\BlueTeamController::class, 'join'])->name('blueteam/join');
Route::any('/blueteam/{page}', [App\Http\Controllers\BlueTeamController::class, 'page'])->name('blueteam');
Route::any('/redteam/create', [App\Http\Controllers\RedTeamController::class, 'create'])->name('redteam/create');
Route::any('/redteam/{page}', [App\Http\Controllers\RedTeamController::class, 'page'])->name('redteam');
Route::any('/admin/{page}', [App\Http\Controllers\AdminController::class, 'page'])->name('admin');
Route::any('/setup', [App\Http\Controllers\SetupController::class, 'page'])->name('setup');
