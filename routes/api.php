<?php


use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\TodosController;
use Illuminate\Support\Facades\Route;

// guest endpoints
Route::post('/register', [UserController::class, 'create']);
Route::post('/auth/login', [AuthController::class, 'login']);
// user endpoints
Route::middleware(['auth:sanctum'])->group(function () {

  Route::post('/auth/logout', [AuthController::class, 'logout']);

  Route::get('/user/profile', [UserController::class, 'show']);
  Route::get('/search', [UserController::class, 'search']); // User-Suche
  Route::patch('/user/username', [UserController::class, 'updateUsername']);
  Route::patch('/user/email', [UserController::class, 'updateEmail']);
  Route::patch('/user/password', [UserController::class, 'updatePassword']);
  Route::patch('/user/avatar', [UserController::class, 'updateAvatar']);
  Route::delete('/user/profile', [UserController::class, 'destroy']);


  Route::get('/todos', [TodosController::class, 'index']);
  Route::post('/todos', [TodosController::class, 'create']);
  Route::patch('/todos', [TodosController::class, 'update']);
  Route::delete('/todos/{id}', [TodosController::class, 'destroy']);
  Route::get('/todos/{id}', [TodosController::class, 'search']);  // Todo-Suche
  Route::get('/tags', [TodosController::class, 'getTags']);

});


