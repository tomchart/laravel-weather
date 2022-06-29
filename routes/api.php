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

Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [SessionController::class, 'store'])->middleware(['api', 'auth:sanctum']);

Route::get('/weather/today/{location}', function (WeatherApi $weatherApi) {
  return $weatherApi->todayLocation(request()->location);
});

Route::get('/weather/forecast/{location}', function (WeatherApi $weatherApi) {
  return $weatherApi->forecastLocation(request()->location);
});
