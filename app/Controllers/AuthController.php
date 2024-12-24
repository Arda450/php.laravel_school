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
    if (!$user) {
      // Rückgabe einer Fehlermeldung, falls kein Benutzer mit dieser E-Mail existiert
      return response()->json([
        'status' => 'error',
        'message' => 'No such user found',
      ], 404);
    }

    // Überprüfung, ob das Passwort korrekt ist
    if (!\Hash::check($password, $user->password)) {
      // Rückgabe einer Fehlermeldung, falls das Passwort falsch ist
      return response()->json([
        'status' => 'error',
        'message' => 'Incorrect password',
      ], 401);
    }

    // Token erstellen, wenn Benutzer erfolgreich authentifiziert wurde
    $token = $user->createToken('bearer');

    // Rückgabe einer Erfolgsmeldung mit dem Token und den Benutzerdaten
    return response()->json([
      'status' => 'success',
      'message' => 'Login successful',
      'token' => $token->plainTextToken,
      'user' => $user,
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
