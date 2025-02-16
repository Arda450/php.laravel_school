<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Todo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
  function run() {
    
    $alex = User::create([
      'username' => 'alex_dev', 
      'email' => 'alex.dev@example.com',
      'password' => 'password',
    ]);

    $arda = User::create([
      'username' => 'arda_coder', 
      'email' => 'arda.coder@example.com',
      'password' => 'password',
    ]);

    $master = User::create([
      'username' => 'todo_master', 
      'email' => 'todo.master@example.com',
      'password' => 'password',
    ]);

    $alexTodo1 = Todo::create([
      'user_id' => $alex->id,
      'title' => 'Frontend Bug Fix',
      'description' => 'Fix responsive design issues in navigation',
      'status' => 'doing',
      'due_date' => now()->addDays(9)->format('d.m.Y'),
      'tags' => json_encode([
        ['id' => '1', 'text' => 'Work'],
        ['id' => '4', 'text' => 'Urgent']
      ])
    ]);

    $alexTodo2 = Todo::create([
      'user_id' => $alex->id,
      'title' => 'Learn TypeScript',
      'description' => 'Complete TypeScript course on Udemy',
      'status' => 'open',
      'due_date' => now()->addWeek()->format('d.m.Y'),
      'tags' => json_encode([
        ['id' => '3', 'text' => 'School']
      ])
    ]);

    $ardaTodo = Todo::create([
      'user_id' => $arda->id,
      'title' => 'Database Optimization',
      'description' => 'Optimize database queries for better performance',
      'status' => 'open',
      'due_date' => now()->addDays(15)->format('d.m.Y'),
      'tags' => json_encode([
        ['id' => '1', 'text' => 'Work'],
        ['id' => '5', 'text' => 'Low Priority']
      ])
    ]);

    $masterTodo = Todo::create([
      'user_id' => $master->id,
      'title' => 'Project Planning',
      'description' => 'Create project timeline and milestones',
      'status' => 'completed',
      'tags' => json_encode([
        ['id' => '1', 'text' => 'Work']
      ])
    ]);

    $sharedTodo1 = Todo::create([
      'user_id' => $alex->id,
      'title' => 'Team Meeting Preparation',
      'description' => 'Prepare presentation for next week\'s team meeting',
      'status' => 'doing',
      'due_date' => now()->addDays(20)->format('d.m.Y'),
      'tags' => json_encode([
        ['id' => '1', 'text' => 'Work'],
        ['id' => '4', 'text' => 'Urgent']
      ])
    ]);
    
    $sharedTodo1->sharedWith()->attach($arda->id, [
      'shared_by_user_id' => $alex->id
    ]);

    $sharedTodo2 = Todo::create([
      'user_id' => $arda->id,
      'title' => 'Code Review',
      'description' => 'Review pull requests for new features',
      'status' => 'open',
      'due_date' => now()->addDays(10)->format('d.m.Y'),
      'tags' => json_encode([
        ['id' => '1', 'text' => 'Work']
      ])
    ]);

    $sharedTodo2->sharedWith()->attach($master->id, [
      'shared_by_user_id' => $arda->id
    ]);
  }
}