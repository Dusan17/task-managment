<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository
{
    // Get all tasks for the authenticated user
    public function getTasks(int $userId): Collection
    {
        return Task::where('user_id', $userId)->get();
    }

    // Create a new task
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    // Get a specific task by ID
    public function findTask(int $taskId): ?Task
    {
        return Task::find($taskId);
    }

    // Update an existing task
    public function updateTask(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    // Delete a task
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    // Mark a task as complete
    public function completeTask(Task $task): bool
    {
        $task->status = true;
        return $task->save();
    }
}
