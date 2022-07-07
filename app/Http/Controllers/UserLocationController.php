<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLocationController extends Controller
{
  public function store(Request $request) {
    $location = $request->validate([
      'location' => ['required'],
    ]);

    if (!auth()->user()) {
      return response('Authentication error', 401);
    };

    if ($location) {
      auth()->user()->location = $location['location'];
      auth()->user()->save();
      return Auth::user();
    };

    return response('No location provided', 400);
  }
}
