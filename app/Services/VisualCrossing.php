<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VisualCrossing implements \App\Contracts\WeatherApi
{
  public function searchLocation(string $location)
  {
    $response = Http::get(config('services.vc.baseUri') . $location, [
      'key' => config('services.vc.apiKey'),
      ]);
    return $response;
  }
}
