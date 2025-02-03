<?php

namespace App\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController {
  function login(Request $request) {
    // Hier wird aus der Anfrage der Wert des Eingabefelds 'email' extrahiert und der Variablen $email zugewiesen.
    $email = $request->input('email');
    $password = $request->input('password');

    // Überprüfung, ob der Benutzer existiert
    $user = User::where('email', $email)->first();
    if (!$user || !\Hash::check($password, $user->password)) {
      // Rückgabe einer Fehlermeldung, falls kein Benutzer mit dieser E-Mail existiert oder das Passwort falsch ist
      return response()->json([
        'status' => 'error',
        'message' => 'Invalid email or password',
      ], 401);
    }

    // Token erstellen, wenn Benutzer erfolgreich authentifiziert wurde
    $token = $user->createToken('bearer');

    // Rückgabe einer Erfolgsmeldung mit dem Token und den Benutzerdaten
    return response()->json([
      'status' => 'success',
      'message' => 'Login successful',
      'token' => $token->plainTextToken,
      'user' => [
        'id' => (string)$user->id,
        'username' => $user->username,
        'email' => $user->email,
        'profile_image' => $user->profile_image,
        'token' => $token->plainTextToken
    ]
], 200);
  }

  function logout(Request $request) {
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