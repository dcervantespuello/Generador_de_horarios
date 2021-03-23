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

// Login para los estudiantes
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', 'CursosController@index')->name('index');

Route::post('/hill_climbing', 'CursosController@hill_climbing')->name('hill_climbing');

Route::post('/simulated_annealing', 'CursosController@simulated_annealing')->name('simulated_annealing');

Route::post('/ant_colony', 'CursosController@ant_colony')->name('ant_colony');

Auth::routes();
