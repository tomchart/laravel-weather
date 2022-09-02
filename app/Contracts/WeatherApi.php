<?php

namespace App\Contracts;

interface WeatherApi
{
  public function parseResponse(String $response);
  public function todayLocation(String $location);
  public function forecastLocation(String $location);
}
