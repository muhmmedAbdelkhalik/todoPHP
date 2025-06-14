<?php

namespace App\Services;

use App\Models\Todo;
use App\Models\User;

class TodoService
{
    public function create(array $data, User $user): Todo
    {
        return Todo::create([
            ...$data,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function update(Todo $todo, array $data): Todo
    {
        $todo->update($data);
        return $todo;
    }

    public function delete(Todo $todo): void
    {
        $todo->delete();
    }
} 