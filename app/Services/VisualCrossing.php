<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class VisualCrossing implements \App\Contracts\WeatherApi
{
  private function parseHour($hour, $datetime) {
    $futureHour = [
      "datetime" => $datetime->format("d/m/y H:i"),
      "date" => $datetime->format("d/m/y"),
      "time" => $datetime->format("H:i"),
      "temp" => $hour->temp,
      "feelsLike" => $hour->feelslike,
      "humidity" => $hour->humidity,
      "pressure" => $hour->pressure,
      "uvIndex" => $hour->uvindex,
      "precip" => $hour->precip,
      "precipProb" => $hour->precipprob,
      "windSpeed" => $hour->windspeed,
      "windDir" => $hour->winddir,
    ];
    return $futureHour;
  }
  
  private function stripFutureHours($responseObj) {
    $tz = $responseObj->timezone;
    $currentDatetime = new Carbon($responseObj->days[0]->datetime . " " . $responseObj->currentConditions->datetime, $tz);
    $futureHours = [];

    foreach ($responseObj->days as $day) {
      $dayDate = new Carbon($day->datetime, $tz);
      if ($currentDatetime->diffInDays($dayDate) < 2) {
        foreach ($day->hours as $hour) {
          $hourDatetime = new Carbon($day->datetime . " " . $hour->datetime, $tz);
          if (
            $currentDatetime->diffInHours($hourDatetime, false) <= 23 &&
            $currentDatetime->diffInHours($hourDatetime, false) >= 0 &&
            $currentDatetime->diffInHours($hourDatetime) % 3 == 0
          ) {
            $futureHours[] = $this->parseHour($hour, $hourDatetime);
          }
        }
      }
    }
    return $futureHours;
  }

  public function parseResponse(String $response) {
    // decode json string into object
    $responseObj = json_decode($response);

    // create datetime object from date, time, timezone strings in response
    $datetime = new Carbon(
      $responseObj->days[0]->datetime . " " . $responseObj->currentConditions->datetime . " ",
      $responseObj->timezone
    );

    // create $parsed array for required data, store currentConditions data
    $parsed = [
      "currentConditions" => [
        "iconName" => $responseObj->currentConditions->icon,
        "temp" => $responseObj->currentConditions->temp,
        "feelsLike" => $responseObj->currentConditions->feelslike,
        "conditions" => $responseObj->currentConditions->conditions,
        "precip" => $responseObj->currentConditions->precip,
        "windSpeed" => $responseObj->currentConditions->windspeed,
        "datetime" => $datetime->toDayDateTimeString(),
        "humanDate" => $datetime->format("l jS \of F Y"),
        "humanTime" => $datetime->format("g:i A"),
        "resolvedAddress" => $responseObj->resolvedAddress,
        "description" => $responseObj->description,
      ],
      "futureHours" => $this->stripFutureHours($responseObj),
      "days" => [],
    ];

    // store day data in $parsed
    foreach ($responseObj->days as $day) {
      $dayParsed = [
        "date" => $day->datetime,
        "tempMax" => $day->tempmax,
        "tempMin" => $day->tempmin,
      ];
      $parsed["days"][] = $dayParsed;
    }

    // encode and return parsed response of required data
    $parsedJson = json_encode($parsed);
    return $parsedJson;
  }

  private function verifyResponse(String $response) {
    if (str_contains($response, "Invalid location found. Please check your location parameter:")) {
      return json_encode([
        "error" => "Invalid location - please check your search parameter.",
      ]);
    } else {
      return null;
    }
  }

  public function todayLocation(string $location)
  {
    $response = Http::get(config('services.vc.baseUri') . $location . '/next24hours', [
      'key' => config('services.vc.apiKey'),
      'unitGroup' => 'uk',
      'include' => 'fcst,obs,histfcst,stats,hours,current,alerts'
      ]);

    // parse response
    $responseParsed = $this->parseResponse($response);
    return $responseParsed;
  }

  public function forecastLocation(string $location)
  {
    $response = Http::get(config('services.vc.baseUri') . $location . '/next7days', [
      'key' => config('services.vc.apiKey'),
      'unitGroup' => 'uk',
      'include' => 'fcst,obs,histfcst,stats,days,hours,current,alerts'
      ]);
    // parse response
    $responseParsed = $this->parseResponse($response);
    return $responseParsed;
  }
}
