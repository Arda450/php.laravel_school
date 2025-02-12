<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
  function run() {
    User::create([
      'username' => 'alex_dev', 
      'email' => 'alex.dev@example.com',
      'password' => 'password',
    ]);


    User::create([
      'username' => 'arda_coder', 
      'email' => 'arda.coder@example.com',
      'password' => 'password',
    ]);


    User::create([
      'username' => 'todo_master', 
      'email' => 'todo.master@example.com',
      'password' => 'password',
    ]);
  }
}