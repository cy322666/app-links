<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

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

Route::group(['prefix' => 'app'], function () {

    Route::get('transition', [WebController::class, 'transition']);
});

Route::group(['prefix' => 'admin'], function () {

    Route::get('link/delete/{link}', [WebController::class, 'deleteLink'])->name('admin.link.delete');

    Route::get('app/delete/{app}', [WebController::class, 'deleteApp'])->name('admin.app.delete');
});
