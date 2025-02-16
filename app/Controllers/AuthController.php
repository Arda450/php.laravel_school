<?php

namespace App\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController {
  function login(Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');
  
    // Nur benötigte Felder laden
    $user = User::select(['id', 'email', 'password', 'username', 'profile_image'])
      ->where('email', $email)
      ->first();
  
    if (!$user || !\Hash::check($password, $user->password)) {
      return response()->json([
        'status' => 'error',
        'message' => 'Invalid email or password',
      ], 401);
    }
  
    // Token nur einmal erstellen
    $token = $user->createToken('bearer')->plainTextToken;

  
    return response()->json([
      'status' => 'success',
      'message' => 'Login successful',
      'user' => [
        'id' => (string)$user->id,
        'username' => $user->username,
        'email' => $user->email,
        'profile_image' => $user->profile_image,
        'token' => $token
      ]
    ], 200);
  }

  function logout() {
    // Abrufen des authentifizierten Benutzers
    $user = \Auth::user();

    // Löschen aller aktiven Tokens des Benutzers
    $user->tokens()->delete();

    // Rückgabe einer Erfolgsmeldung, dass der Benutzer abgemeldet wurde
    return response()->json([
      'status' => 'success',
      'message' => 'Logout successful',
      'user' => $user,
    ], 200);
  }

}