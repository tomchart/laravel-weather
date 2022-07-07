<?php

use App\Contracts\WeatherApi;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [UserController::class, 'store'])->middleware(['api']);
Route::post('/login', [SessionController::class, 'store'])->middleware(['api']);
Route::get('/user', [SessionController::class, 'show'])->middleware(['api']);

Route::post('/location/store', [UserLocationController::class, 'store'])->middleware(['api']);

// to guard these routes add:
// if (Auth::guard('admin')->attempt($credentials)) {
// to controller (controller not currently in use for these routes)
Route::get('/weather/today/{location}', function (WeatherApi $weatherApi) {
  return $weatherApi->todayLocation(request()->location);
})->middleware(['api']);

Route::get('/weather/forecast/{location}', function (WeatherApi $weatherApi) {
  return $weatherApi->forecastLocation(request()->location);
})->middleware(['api']);
