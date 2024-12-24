<?php

namespace App\Models;

use Config\Model;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use WendellAdriel\Lift\Attributes\Column;
use WendellAdriel\Lift\Attributes\Hidden;

class User extends Model
{
    use AuthenticatableTrait;
    use HasApiTokens;

    protected $table = 'users';

    #[Column]
    public string $username;

    #[Column]
    public string $email;

    #[Column] #[Hidden]
    public string $password;

    #[Column]
    public ?string $profile_image; // Das ? vor string erlaubt null Werte

    // one-to-many Beziehung. Ein User kann mehrere Artikel haben
    // Laravel erwartet, dass die "articles" Tabelle eine "user_id" spalte hat, die den Benutzer referenziert.
    // function articles(): HasMany|Article {
    //   return $this->hasMany(Article::class);
    // }

    // one-to-many Beziehung. Ein User kann mehrere Kommentare haben
    // Laravel erwartet, dass die "comments" Tabelle eine "user_id" spalte hat, die den Benutzer referenziert.

    // function comments(): HasMany|Comment {
    //   return $this->hasMany(Comment::class);
    // }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class, 'user_id');
    }

    public function sharedTodos(): BelongsToMany
    {
        return $this->belongsToMany(Todo::class, 'shared_todos', 'shared_with_user_id', 'todo_id')
            ->withPivot('shared_by_user_id')
            ->withTimestamps();
    }

    static function validate(Request $request, $isUpdate = false) {
        return $request->validate([
            'username' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,' . ($isUpdate ? $request->user()->id : null)],
            'password' => $isUpdate ? ['nullable', 'string', 'min:8', 'max:20', 'confirmed'] : ['required', 'string', 'min:8', 'max:20', 'confirmed'],
            'current_password' => ['sometimes', 'required', 'string'],
            'new_password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['nullable', 'string', 'max:255'],
        ]);
    }

    // booted wird aufgerufen, wenn das Model geladen wird
    // booted func stellt sicher, dass das Password eines Benutzers immer gehasht wird, bevor es gesp. wird
    static function booted() {
        self::saving(function (User $user) {
            // isDirty überprüft, ob das 'password' Attribut geändert wurde.
            if ($user->isDirty('password')) {
                $plain = $user->getAttribute('password');
                // wenn isDirty = true, wird das neue Passwort neu gehasht bevor es in die DB gesp. wird
                $user->setAttribute('password', \Hash::make($plain));
            }
        });
    }
}
