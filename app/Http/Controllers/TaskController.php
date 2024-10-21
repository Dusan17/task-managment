<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    // Get all tasks
    public function index(Request $request)
    {
        $tasks = $this->taskService->getAllTasks($request->user()->id);
        return response()->json($tasks);
    }

    // Create a new task
    public function store(TaskRequest $request)
    {
        $task = $this->taskService->createTask([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => false,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($task, 201);
    }

    // Update an existing task
    public function update(TaskRequest $request, $id)
    {
        $updated = $this->taskService->updateTask($id, $request->only('title', 'description', 'due_date'));

        if ($updated) {
            return response()->json(['message' => 'Task updated successfully']);
        }

        return response()->json(['message' => 'Task not found'], 404);
    }

    // Delete a task
    public function destroy($id)
    {
        $deleted = $this->taskService->deleteTask($id);

        if ($deleted) {
            return response()->json(['message' => 'Task deleted successfully']);
        }

        return response()->json(['message' => 'Task not found'], 404);
    }

    // Mark a task as complete
    public function complete($id)
    {
        $completed = $this->taskService->completeTask($id);

        if ($completed) {
            return response()->json(['message' => 'Task marked as completed']);
        }

        return response()->json(['message' => 'Task not found'], 404);
    }
}

