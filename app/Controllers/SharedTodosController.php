<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Log;

class SharedTodosController {

    // Anzeigen der Todos, die der Benutzer geteilt hat oder die mit ihm geteilt wurden
    // public function showSharedTodos(Request $request) {
    //     try {
    //         // Aktuell authentifizierter User wird abgerufen
    //         $user = Auth::user();

    //           // Todos, die der eingeloggtee Benutzer geteilt hat, abrufen
    //         $sharedTodos = Todo::whereHas('sharedBy', function($query) use ($user) {
    //         $query->where('shared_by_user_id', $user->id);
    //         })->get();

    //         // Todos, die mit dem eingeloggten Benutzer geteilt wurden, abrufen
    //         $receivedTodos = Todo::whereHas('sharedWith', function($query) use ($user) {
    //             $query->where('shared_with_user_id', $user->id);
    //         })->get();

    //         return response()->json([
    //             'sharedTodos' => $sharedTodos,
    //             'receivedTodos' => $receivedTodos,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'An error occurred while fetching shared todos.',
    //             'message' => $e->getMessage()  // Detaillierte Fehlerbeschreibung
    //         ], 500);
    //     }
    // }

    // Eine Todo mit einem anderen Benutzer teilen
    public function shareTodo(Request $request) {
      Log::info($request);
        

        try {
          $validated = $request->validate([
            'todo_id' => 'required|exists:todos,id',
            'username' => 'required|exists:users,username'
        ]);
            $todo = Todo::findOrFail($validated['todo_id']);
            $user = Auth::user();
            $shareWithUser = User::where('username', $validated['username'])->first();

            
            // Überprüfen, ob der Benutzer der Besitzer des Todos ist
            if ($todo->user_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            // Hier wird verhindert, dass der User das Todo mit sich selbst teit
            if ($shareWithUser->id === $user->id) {
                return response()->json(['error' => 'Cannot share todo with yourself'], 400);
            }

            // Überprüfen hier, ob das Todo bereits geteilt wurde
            if($todo->sharedWith()->where('shared_with_user_id', $shareWithUser->id)->exists()) { 
                return response()->json(['error' => 'Todo already shared with this user.'], 400);
            }

            // das Todo mit dem anderen User verknüpfen
            $todo->sharedWith()->attach($shareWithUser, ['shared_by_user_id' => $user->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Todo shared successfully'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error sharing todo: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to share todo'], 500);
        }
    }

    // Teilen einer Todo entziehen
    public function revokeShare(Request $request) {
        $request->validate([
            'todo_id' => 'required|exists:todos,id',
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $todo = Todo::find($request->input('todo_id'));
            $user = Auth::user();

            // Überprüfen, ob der Benutzer der Besitzer des Todos ist
            if ($todo->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Entfernen der Berechtigung für den anderen Benutzer
            $todo->sharedWith()->detach($request->input('user_id'));

            return response()->json(['message' => 'Share revoked successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while revoking share.'], 500);
        }
    }

    // Aktualisieren einer geteilten Todo
    public function updateSharedTodo(Request $request) {
        $request->validate([
            'todo_id' => 'required|exists:todos,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:255',
        ]);

        try {
            $todo = Todo::find($request->input('todo_id'));
            $user = Auth::user();

            // Überprüfen, ob der Benutzer die Berechtigung hat, die Todo zu aktualisieren
            if ($todo->user_id !== $user->id && !$todo->sharedWith->contains($user->id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Aktualisieren der Todo
            $todo->update($request->only(['title', 'description']));

            return response()->json(['message' => 'Todo updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the todo.'], 500);
        }
    }

    // Löschen einer geteilten Todo
    public function deleteSharedTodo(Request $request) {
        $request->validate([
            'todo_id' => 'required|exists:todos,id'
        ]);

        try {
            $todo = Todo::find($request->input('todo_id'));
            $user = Auth::user();

            // Überprüfen, ob der Benutzer die Berechtigung hat, die Todo zu löschen
            if ($todo->user_id !== $user->id && !$todo->sharedWith->contains($user->id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Löschen der Todo
            $todo->delete();

            return response()->json(['message' => 'Todo deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the todo.'], 500);
        }
    }
}
