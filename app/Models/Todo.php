<?php

namespace App\Models;

use Carbon\Carbon;
use Config\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WendellAdriel\Lift\Attributes\Column;

class Todo extends Model {
  protected $table = 'todos';
  protected $fillable = ['title', 'description', 'due_date', 'status', 'tags',  'user_id'];


  protected $casts = [
    'tags' => 'array',
    'due_date' => 'date:d.m.Y'
];

 // Virtuelle Eigenschaft für shared_with
 protected $appends = ['shared_with'];

  // Beziehung zum Besitzer des Todos
  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
}

public function getSharedWithAttribute()
{
    return $this->sharedWith()->pluck('username');
}

// Die canAccess Methode stellt sicher, dass nur berechtigte Benutzer Änderungen vornehmen können

  public function canAccess(User $user): bool
  {
      return $this->user_id === $user->id || $this->sharedWith->contains($user);
  }

  public function sharedWith(): BelongsToMany {
    return $this->belongsToMany(User::class, 'shared_todos', 'todo_id', 'shared_with_user_id')
    ->withPivot('shared_by_user_id')
    ->withTimestamps();
  }

  public function sharedBy(): BelongsToMany {
    return $this->belongsToMany(User::class, 'shared_todos', 'todo_id', 'shared_by_user_id')
    ->withPivot('shared_with_user_id')
    ->withTimestamps();
  }

  // Hilfsmethode zum Teilen des Todos mit einem Benutzer
  public function shareWith(User $user, User $sharedBy): void 
  {
      if (!$this->sharedWith->contains($user->id)) {
          $this->sharedWith()->attach($user->id, [
              'shared_by_user_id' => $sharedBy->id
          ]);
      }
  }

  // Überprüft, ob das Todo mit einem bestimmten Benutzer geteilt wurde
  public function isSharedWith(User $user): bool 
  {
      return $this->sharedWith->contains($user);
  }

  // Hilfsmethode zum Entfernen der Freigabe für einen Benutzer
  public function unshareWith(User $user): void 
  {
      $this->sharedWith()->detach($user->id);
  }


  public function formatForResponse(): array {
    return [
        'id' => (string) $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'due_date' => $this->due_date ? Carbon::parse($this->due_date)->format('d.m.Y') : null,
        'status' => $this->status,
        'tags' => is_string($this->tags) ? json_decode($this->tags, true) : $this->tags,
        'shared_with' => $this->sharedWith()->pluck('username')->toArray(),
        'shared_by' => $this->user->username,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
    ];
  } 
}