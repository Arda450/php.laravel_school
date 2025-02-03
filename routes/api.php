<?php


use App\Controllers\AuthController;
use App\Controllers\MailsController;
use App\Controllers\UploadsController;
use App\Controllers\UserController;
use App\Controllers\TodosController;
use App\Controllers\SharedTodosController;
use Illuminate\Support\Facades\Route;

// guest endpoints
Route::post('/register', [UserController::class, 'create']);
Route::post('/auth/login', [AuthController::class, 'login']);
// user endpoints
Route::middleware(['auth:sanctum'])->group(function () {
  Route::post('/auth/logout', [AuthController::class, 'logout']);


  Route::delete('/user/profile', [UserController::class, 'destroy']);
  Route::get('/user/profile', [UserController::class, 'show']);

  Route::patch('/user/username', [UserController::class, 'updateUsername']);
  Route::patch('/user/email', [UserController::class, 'updateEmail']);
  Route::patch('/user/password', [UserController::class, 'updatePassword']);
  Route::patch('/user/avatar', [UserController::class, 'updateAvatar']);

  // Route::get('/todos/{id}', [TodosController::class, 'show']);
  Route::get('/todos', [TodosController::class, 'index']);

  // die create methode wird aufgerufen, wenn diese route verwendet wird
  // Client (next.js) greift auf '/todos' zu und sendet die Daten an die create methode
  Route::post('/todos', [TodosController::class, 'create']);
  Route::patch('/todos', [TodosController::class, 'update']);
  Route::delete('/todos/{id}', [TodosController::class, 'destroy']);
  Route::get('/todos/{id}', [TodosController::class, 'search']);  // Todo-Suche

  Route::get('/tags', [TodosController::class, 'getTags']);
  Route::get('/search', [UserController::class, 'search']); // User-Suche

    // Todo Sharing Routes
    Route::post('/todos/share', [SharedTodosController::class, 'shareTodo']);
    Route::delete('/todos/{todo}/share', [SharedTodosController::class, 'revokeShare']);
    Route::get('/todos/shared', [SharedTodosController::class, 'index']);

  // Route::get('/shared-todos', [SharedTodosController::class, 'showSharedTodos']);
  // Route::post('/shared-todos/share', [SharedTodosController::class, 'shareTodo']);
  // Route::delete('/shared-todos/revoke', [SharedTodosController::class, 'revokeShare']);
  // Route::patch('/shared-todos/update', [SharedTodosController::class, 'updateSharedTodo']);
  // Route::delete('/shared-todos/delete', [SharedTodosController::class, 'deleteSharedTodo']);



  
  
  
  
  
  Route::post('/uploads', [UploadsController::class, 'create']);
  Route::delete('/uploads', [UploadsController::class, 'destroy']);

  Route::post('/mails/send', [MailsController::class, 'send']);
});


