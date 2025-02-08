<?php

namespace App\Controllers;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
// Wird verwendet, wenn das findOrFail-Kommando keine passende Todo findet.
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;
use Carbon\Carbon;


// \Exception Fängt alle anderen unerwarteten Fehler ab und gibt eine 500er-Fehlermeldung zurück.

class TodosController {
  public function index()
  {
      try {
          $user = \Auth::user();
          $todos = $user->todos()
              ->with(['user', 'sharedWith'])
              ->get()
              ->map->formatForResponse();

          // Hole auch geteilte Todos
          $sharedTodos = $user->sharedTodos()
              ->with(['user', 'sharedWith'])
              ->get()
              ->map->formatForResponse();

          return response()->json([
              'status' => 'success',
              'todos' => $todos,
              'shared_todos' => $sharedTodos
          ]);

      } catch (\Exception $e) {
          Log::error('Error fetching todos:', ['error' => $e->getMessage()]);
          return response()->json([
              'status' => 'error',
              'message' => 'Error fetching todos',
              'error' => $e->getMessage()
          ], 500);
      }
  }

// index endet hier

public function getTags() {
  $tags = [
      ['id' => '1', 'text' => 'Work'],
      ['id' => '2', 'text' => 'Personal'],
      ['id' => '3', 'text' => 'School'],
      ['id' => '4', 'text' => 'Urgent'],
      ['id' => '5', 'text' => 'Low Priority']
  ];

  return response()->json([
      'status' => 'success',
      'tags' => $tags
  ]);
}

// getTags endet hier

  function create(Request $request) {
    try{
      $payload = $request->validate([
        'title' => 'required|min:1|max:200',
        'description' => 'required|min:1|max:20000',
        'status' => 'nullable|in:open,doing,completed',
        'tags' => 'nullable|array',
        'tags.*.id' => 'required|string',
        'tags.*.text' => 'required|in:Work,Personal,School,Urgent,Low Priority',
        'due_date' => 'nullable|date|after_or_equal:today',
        'shared_with' => 'nullable|array', // Geändert zu array
        'shared_with.*' => 'string|exists:users,username' // Validierung für jedes Array-Element
    ]);


     // Erstelle zuerst das Todo
     $todo = \Auth::user()->todos()->create([
      'title' => $payload['title'],
      'description' => $payload['description'],
      'status' => $payload['status'] ?? 'open',
      'due_date' => $payload['due_date'],
      'tags' => $payload['tags']
  ]);

    
         // Teilen mit mehreren Benutzern
         if (!empty($payload['shared_with'])) {
          $sharedUsers = User::whereIn('username', $payload['shared_with'])->get();
          foreach ($sharedUsers as $sharedUser) {
              $todo->sharedWith()->attach($sharedUser->id, [
                  'shared_by_user_id' => auth()->id()
              ]);
          }
      }


    // das erfolgreich erstellte todo wird zurückgegeben
    return response()->json([
      'status' => 'success',
      'message' => 'To-Do created successfully',
      'todo' => $todo->formatForResponse(),
    ], 201);
  } catch (ValidationException $e) {
    // Rückgabe von Validierungsfehlern
    return response()->json([
      'status' => 'error',
      'message' => 'Validation failed',
      'errors' => $e->errors(),
    ], 422);
  } catch (\Exception $e) {
    // Rückgabe von Validierungsfehlern
    return response()->json([
      'status' => 'error',
      'message' => 'An error occured while creating the To-Do',
      'error' => $e->getMessage(),
    ], 500);
  }
}



  // aktualisieren eines bestehenden todos
  function update(Request $request) {
    try {
       // Die ID des zu aktualisierenden Todos wird aus der Anfrage extrahiert.
    $id = $request->input('id');
    // Die NutzerEINGABEN werden validiert und die Daten werden in $payload gespeichert.
    // $payload = Todo::validate($request);

    $user = \Auth::user();

     // Finde Todo mit Zugriffsberechtigung
     $todo = Todo::where('id', $id)
     ->where(function($query) use ($user) {
         $query->where('user_id', $user->id)
             ->orWhereHas('sharedWith', function($q) use ($user) {
                 $q->where('shared_with_user_id', $user->id);
             });
     })
     ->firstOrFail();

    $payload = $request->validate([
      'title' => 'sometimes|required|min:1|max:200',
      'description' => 'sometimes|required|min:1|max:20000',
      'status' => 'nullable|required|in:open,doing,completed',
      'tags' => 'nullable|array',
      'tags.*.id' => 'required|string',
      'tags.*.text' => 'required|in:Work,Personal,School,Urgent,Low Priority',
      'due_date' => 'nullable|date|after_or_equal:today', // due_date muss ein gültiges Datum sein und heute oder später
      // 'shared_with' => 'nullable|array',
      // 'shared_with.*' => 'string|exists:users,username'
    ]);

    // Tags als JSON speichern, falls angegeben
    if ($request->has('tags')) {
      $payload['tags'] = json_encode($request->input('tags'));
  }


    // das Todo aktualisieren
    $todo->update($payload);

    // Formatierte Antwort zurückgeben
    return response()->json([
      'status' => 'success',
      'message' => 'To-Do updated successfully',
      'todo' => $todo->formatForResponse()
  ], 200);

  } catch (ValidationException $e) {
      // Rückgabe von Validierungsfehlern
      return response()->json([
      'status' => 'error',
      'message' => 'Validation failed',
      'errors' => $e->errors(),
    ], 422); //422 = Validierungsfehler

   } catch (ModelNotFoundException $e) {
    // To-Do wurde nicht gefunden
    return response()->json([
      'status' => 'error',
      'message' => 'To-Do not found',
    ], 404);

  } catch (\Exception $e) {
    // Allgemeiner Fehler, falls etwas Unerwartetes passiert
    Log::error('Update failed:', [
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString()
  ]);
    return response()->json([
      'status' => 'error',
      'message' => 'An error occurred while updating the To-Do',
    ], 500);
  }
}



  // Löschen eines Todos
  public function destroy($id) {
    try {
        $user = \Auth::user();
       // Suche das Todo unabhängig vom Besitzer
        $todo = Todo::findOrFail($id);
        
        // Prüfe ob der User der Besitzer ist
        if ($todo->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: You can only delete your own todos'
            ], 403);
        }
        
      // Löschen des Todos
      $todo->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'To-Do deleted successfully',
      ], 200);

    } catch (ModelNotFoundException $e) {
      // To-Do wurde nicht gefunden
      return response()->json([
        'status' => 'error',
        'message' => 'To-Do not found',
      ], 404);

    } catch (\Exception $e) {
      // Fehlermeldung für nicht gefundenes oder nicht löschbares To-Do
      return response()->json([
        'status' => 'error',
        'message' => 'To-Do could not be deleted',
      ], 500); // 500 = Serverfehler
    }
  }




// für das suchen eines einzelnen todos
  public function search($id) {
    try {
        // Hole das Todo und prüfe ob der User darauf zugreifen darf
        $user = \Auth::user();
        $todo = Todo::findOrFail($id);
        // Prüfe ob der User Zugriff auf das Todo hat
        if (!$todo->canAccess($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }
        // Sonst gebe das formatierte Todo zurück
        return response()->json([
            'status' => 'success',
            'todo' => $todo->formatForResponse()
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Todo not found'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while fetching the todo'
        ], 500);
    }
  }

}