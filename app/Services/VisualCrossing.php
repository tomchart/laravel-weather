<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VisualCrossing implements \App\Contracts\WeatherApi
{
  public function todayLocation(string $location)
  {
    $response = Http::get(config('services.vc.baseUri') . $location . '/today', [
      'key' => config('services.vc.apiKey'),
      'unitGroup' => 'uk',
      'include' => 'fcst,obs,histfcst,stats,hours,current,alerts'
      ]);
    return $response;
  }
  public function forecastLocation(string $location)
  {
    $response = Http::get(config('services.vc.baseUri') . $location, [
      'key' => config('services.vc.apiKey'),
      'unitGroup' => 'uk',
      'include' => 'fcst,obs,histfcst,stats,days,hours,current,alerts'
      ]);
    return $response;
  }
}
