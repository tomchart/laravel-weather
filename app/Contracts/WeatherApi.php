<?php

namespace App\Contracts;

interface WeatherApi
{
  public function searchLocation(String $location);
}
