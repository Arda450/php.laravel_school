s<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  function up() {
    Schema::create('shared_todos', function (Blueprint $table) {
      // foreign key to the 'todos' table
      $table->foreignId('todo_id')->constrained('todos')->cascadeOnDelete();
      // foreign key to the 'users' table, who is the recipient of the shared todo
      $table->foreignId('shared_with_user_id')->constrained('users')->cascadeOnDelete();
      // foreign key to the 'users' table, who shared the todo

      $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();


      $table->primary(['todo_id', 'shared_with_user_id']); // Verhindert Duplikate
      $table->timestamps();

    });

  }

  function down() {
    Schema::dropIfExists('shared_todos');
  }
};