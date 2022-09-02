<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class VisualCrossing implements \App\Contracts\WeatherApi
{
  private function parseHour($hour, $datetime) {
    $futureHour = [
      "datetime" => $datetime,
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
          $dayDatetime = new Carbon($day->datetime . " " . $hour->datetime, $tz);
          if ($currentDatetime->diffInHours($dayDatetime, false) <= 23 && $currentDatetime->diffInHours($dayDatetime, false) >= 0) {
            $futureHours[] = $this->parseHour($hour, $day->datetime);
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
    $datetime = date_create_from_format(
      "Y-m-d H:i:s T",
      $responseObj->days[0]->datetime . " " . $responseObj->currentConditions->datetime . " " . $responseObj->timezone
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
        "datetime" => date_format($datetime, "Y-m-d H:i:s T"),
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
    return $response;
  }
}
