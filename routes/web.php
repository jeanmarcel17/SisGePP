<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AnimalController;

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
    return view('index');
});

Auth::routes();

/*
-- Controller: Autenticação
*/

Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

/*
-- Controller: Rebanho
*/
Route::get('/rebanho', [App\Http\Controllers\RebanhoController::class, 'index']);
Route::get('lote', [App\Http\Controllers\LoteController::class, 'index']);
Route::get('animal', 'App\Http\Controllers\RebanhoController@animal');
//Route::get('lote', 'App\Http\Controllers\RebanhoController@lote');


/*
-- Controller: AnimalController
*/
Route::get('animal', [AnimalController::class, 'index']);
Route::post('animal', [AnimalController::class, 'create']);
Route::get('read', [AnimalController::class, 'read']);
//Route::get('animal', [AnimalController::class, 'listraca']);
// Route::get('edit-student/{id}', [AnimalController::class, 'edit']);
// Route::put('update-student/{id}', [AnimalController::class, 'update']);
// Route::delete('delete-student/{id}', [AnimalController::class, 'destroy']);



