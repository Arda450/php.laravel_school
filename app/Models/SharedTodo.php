<?php

namespace App\Models;

use Config\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use WendellAdriel\Lift\Attributes\Column;

// dient als pivot table für die todos und die users
// verwaltet die metadaten der geteilten todos (wer hat was geteilt, wann, an wen)
// die sharedtodoscontroller.php datei konnte gelöscht werden, weil alles in den todoscontroller integriert wurde 

class SharedTodo extends Model {
    protected $fillable = ['todo_id', 'shared_with_user_id', 'shared_by_user_id'];

    /**
     * Relationship to the Todo that is being shared.
     * Defines a "belongs to" relationship to the Todo model.
     */
    public function todo(): BelongsTo {
        return $this->belongsTo(Todo::class, 'todo_id');
    }

    /**
     * Relationship to the user who the todo is shared with.
     * Defines a "belongs to" relationship to the User model.
     */
    public function sharedWithUser(): BelongsTo {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    /**
     * Relationship to the user who shared the todo.
     * Defines a "belongs to" relationship to the User model.
     */
    public function sharedByUser(): BelongsTo {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }
}