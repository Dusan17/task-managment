<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Illuminate\Support\Collection;
use App\Models\Task;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    // Retrieve all tasks for the authenticated user
    public function getAllTasks(int $userId): Collection
    {
        return $this->taskRepository->getTasks($userId);
    }

    // Create a new task
    public function createTask(array $data): Task
    {
        return $this->taskRepository->createTask($data);
    }

    // Retrieve a specific task by ID
    public function getTaskById(int $taskId): ?Task
    {
        return $this->taskRepository->findTask($taskId);
    }

    // Update a task
    public function updateTask(int $taskId, array $data): bool
    {
        $task = $this->taskRepository->findTask($taskId);

        if ($task) {
            return $this->taskRepository->updateTask($task, $data);
        }

        return false;
    }

    // Delete a task
    public function deleteTask(int $taskId): bool
    {
        $task = $this->taskRepository->findTask($taskId);

        if ($task) {
            return $this->taskRepository->deleteTask($task);
        }

        return false;
    }

    // Mark a task as complete
    public function completeTask(int $taskId): bool
    {
        $task = $this->taskRepository->findTask($taskId);

        if ($task) {
            return $this->taskRepository->completeTask($task);
        }

        return false;
    }
}
