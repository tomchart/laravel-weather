<?php

namespace App\Contracts;

interface WeatherApi
{
  public function todayLocation(String $location);
  public function forecastLocation(String $location);
}
