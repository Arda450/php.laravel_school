<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up() {
    Schema::create('todos', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->text('description');
      $table->date('due_date')->nullable(); // fälligkeitsdatum
      $table->enum('status', ['open', 'doing', 'completed'])->default('open'); // wie "open", "completed" ...
      $table->json('tags')->nullable();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Besitzer des Todos
      $table->timestamps();
    });

  }

  public function down() {
    Schema::dropIfExists('todos');
  }
};