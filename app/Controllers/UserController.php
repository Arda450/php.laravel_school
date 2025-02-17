<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Log;

class UserController {
    function show() {
        try {
            $user = \Auth::user()->fresh();
            $user->profile_image_url = $user->profile_image_url;
            return response()->json([
                'status' => 'success',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

  // Erstelle einen neuen Benutzer
  function create(Request $request) {
    try {
        $payload = User::validate($request);
        $user = User::create($payload);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while creating the user.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



public function updateUsername(Request $request)
{
    try {
        $user = \Auth::user();
        $validated = $request->validate([
            'username' => [ 'required',
                'string',
                'max:20',
                'unique:users,username,' . $user->id,
                function ($attribute, $value, $fail) use ($user) {
                // Prüfe, ob die neue username anders ist als die aktuelle
                if ($value === $user->username) {

                    $fail('New username cannot be the same as the current username.');
                }
            },
        ],
        ]);

        // Debug-Logging
        \Log::info('Username update attempt:', [
            'user_id' => $user->id,
            'old_username' => $user->username,
            'new_username' => $validated['username']
        ]);

        $user->username = $validated['username'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Username updated successfully',
            'user' => $user->fresh()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

public function updateEmail(Request $request)
{
    try {
        $user = \Auth::user();
        
        // Erweiterte Validierung
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
                function ($attribute, $value, $fail) use ($user) {
                    // Prüfe, ob die neue E-Mail anders ist als die aktuelle
                    if ($value === $user->email) {
                        $fail('New email cannot be the same as the current email.');
                    }
                },
            ],
        ]);

        // // Debug-Logging
        // \Log::info('Username update attempt:', [
        //     'user_id' => $user->id,
        //     'old_username' => $user->username,
        //     'new_username' => $validated['email']
        // ]);

        $user->email = $validated['email'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Email updated successfully',
            'user' => $user->fresh()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

public function updatePassword(Request $request)
{
    try {
        $user = \Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                function ($attribute, $value, $fail) use ($request, $user) {
                    // Prüfe, ob das aktuelle Passwort korrekt ist
                    if (!\Hash::check($request->current_password, $user->password)) {
                        $fail('Current password is incorrect');
                    }
                    // Prüfe, ob das neue Passwort anders ist als das aktuelle
                    if (\Hash::check($value, $user->password)) {
                        $fail('New password cannot be the same as the current password.');
                    }

                },
            ],
            'password_confirmation' => 'required|string'
        ]);

        // // Debug-Logging
        // \Log::info('Username update attempt:', [
        //     'user_id' => $user->id,
        //     'old_username' => $user->username,
        //     'new_username' => $validated['email']
        // ]);

        // Überprüfung des aktuellen Passworts. wird im UI als toast error angezeigt
        if (!\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Setze hier das neue Passwort
        $user->password = $validated['password'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully',
            'user' => $user
        ]);
     } catch (\Exception $e) {
        \Log::error('Password update error:', [
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

public function updateAvatar(Request $request) {
    try {
        $user = \Auth::user();
        $validated = $request->validate([
            'profile_image' => 'required|string',
        ]);

        $user->profile_image = $validated['profile_image'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar updated successfully',
            'user' => $user->fresh()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}







public function destroy(Request $request) {
    try {
        $user = \Auth::user();
      
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        if ($validated['email'] !== $user->email) {
        return response()->json([
            'status' => 'error',
            'message' => 'Email does not match our records.',
        ], 400);
    }


    // Füge Logging hinzu
    \Log::info('Attempting to delete user:', [
        'user_id' => $user->id,
        'email' => $user->email
    ]);

        $user->tokens()->delete();

        $user->delete();

         // Füge Logging hinzu
         \Log::info('User deleted successfully');

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
      ], 200);
  } catch (\Exception $e) {

     // Füge Fehler-Logging hinzu
     \Log::error('Error deleting user:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

      return response()->json([
          'status' => 'error',
          'message' => 'An error occurred while deleting the user.',
          'error' => $e->getMessage(),
      ], 500);
  }
}

  // To-Do mit einem anderen Benutzer teilen
  function shareTodoWithUser(Request $request, $todoId) {
    try {
        // Hole das To-Do des angemeldeten Benutzers
        $todo = \Auth::user()->todos()->findOrFail($todoId);

        // Validiere die Anfrage
        $payload = $request->validate([
            'shared_with_user_id' => 'required|exists:users,id'
        ]);

        // Füge das Teilen in die shared_todos-Tabelle ein
        $todo->sharedWith()->attach($payload['shared_with_user_id'], [
            'shared_by_user_id' => \Auth::id()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'To-Do successfully shared'
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'To-Do not found'
        ], 404);
    } catch (\Exception $e) {
      return response()->json([
          'status' => 'error',
          'message' => 'An error occurred while sharing the To-Do.',
          'error' => $e->getMessage(),
      ], 500);
    }
  }

  public function search(Request $request)
{
    try {
        Log::info('User search request:', ['term' => $request->get('term')]);

        $term = $request->get('term');
        
         if (empty($term)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Suchbegriff erforderlich'
            ], 400);
        }

        $users = User::where('username', 'LIKE', "%{$term}%")
                    ->where('id', '!=', auth()->id())
                    ->get(['id', 'username']);

                    Log::info('User search results:', ['count' => $users->count()]);

        return response()->json([
            'status' => 'success',
            'users' => $users
        ]); 

    } catch (\Exception $e) {
        Log::error('User search error:', ['error' => $e->getMessage()]);
        return response()->json([
            'status' => 'error',
            'message' => 'Fehler bei der Benutzersuche',
            'error' => $e->getMessage()
        ], 500);
    }
}
  
}