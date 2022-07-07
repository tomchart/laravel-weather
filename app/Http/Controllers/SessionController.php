<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
  public function store(Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $remember = true)) {
        $request->session()->regenerate();

        return Auth::user();
    }

    return response('Authentication error', 401);

    // return [
    //     'email' => 'The provided credentials do not match our records.',
    // ];
  }

  public function show() {
    return Auth::user();
  }

  public function destroy(Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return [
      'message' => 'logged out',
    ];
  }
}
