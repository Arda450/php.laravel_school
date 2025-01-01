<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Log;

class UserController {
  function show(Request $request) {
    return response()->json([
      'status' => 'success',
      'user' => \Auth::user(),
  ], 200);
  }


  // // Erstelle einen neuen Benutzer
  // function create(Request $request) {
  //   $payload = User::validate($request);
  //   return User::create($payload);
  // }

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

   // Aktualisiere den Benutzer
//    function update(Request $request) {
//     try {
//         $user = \Auth::user();
//         // Benutze die validate Methode aus dem User Model
//         $payload = User::validate($request, true);
//         \Log::info('Payload nach Validation:', $payload);
//         // Debug-Logging
//         \Log::info('Update request received', [
//             'request_data' => $request->all(),
//             'user_id' => $user->id
//         ]);

//         // Überprüfe, ob das aktuelle Passwort bereitgestellt wurde und ob es korrekt ist
//         if (isset($payload['current_password'])) {
//             if (!\Hash::check($payload['current_password'], $user->password)) {
//                 return response()->json([
//                     'status' => 'error',
//                     'message' => 'Current password is incorrect.',
//                 ], 401); // 401 Unauthorized
//             }

//             // Setze das neue Passwort, wenn es bereitgestellt wurde
//             if (isset($payload['new_password'])) {
//                 $user->password = $payload['new_password']; // Das Passwort wird in der booted-Methode gehasht
//             }
//         }

//         // Aktualisiere den Benutzernamen und die E-Mail, wenn sie bereitgestellt wurden
//         if (isset($payload['username'])) {
//             $user->username = $payload['username'];
//         }
        
//         if (isset($payload['email'])) {
//             $user->email = $payload['email'];
//         }

//         if ($request->hasFile('profile_image')) {
//             $path = $request->file('profile_image')->store('profile_images', 'public');
//             $user->profile_image = $path;
//         }


        
//         // Debug-Logging vor dem Speichern
//         \Log::info('About to save user changes', [
//             'changes' => $user->getDirty()
//         ]);

//         // Speichere die Änderungen
//         $user->save();

//         if (!$user->wasChanged()) {
//             \Log::warning('Keine Änderungen gespeichert!', $user->getAttributes());
//         }
        
//         // Debug-Logging nach dem Speichern
//         \Log::info('User updated successfully', [
//             'user' => $user->toArray()
//         ]);

//         return response()->json([
//             'status' => 'success',
//             'message' => 'User updated successfully.',
//             'user' => $user,
//         ], 200);
//     } catch (ValidationException $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Validation failed',
//             'errors' => $e->errors(),
//         ], 422);
//     } catch (ModelNotFoundException $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'User not found',
//         ], 404);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'An error occurred while updating the user.',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }


public function update(Request $request)
{
    try {
        $user = \Auth::user();

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|max:20|confirmed',
            'current_password' => 'required_with:password|string',
            'profile_image' => 'sometimes|file|image|max:2048',
        ]);

         // Überprüfung des aktuellen Passworts
         if (isset($validated['password'])) {
            if (!\Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect',
                ], 400);
            }

            // Neues Passwort hashen
            $validated['password'] = bcrypt($validated['password']);
        }

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $validated['profile_image'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 400);
    }
}


function destroy(Request $request) {
  try {
      $user = \Auth::user();
      $user->delete();
      return response()->json([
          'status' => 'success',
          'message' => 'User deleted successfully.',
      ], 200);
  } catch (\Exception $e) {
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
        ]);  // Explizit JSON-Header setzen

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