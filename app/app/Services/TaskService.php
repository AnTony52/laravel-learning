<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function create(array $data): Task
    {
        if (!isset($data['status'])) {
            $data['status'] = 'todo';
        }

        $data['title'] = trim($data['title']);

        return Task::create($data);

    }

    public function update(Task $task, array $data): Task
    {
        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);
        }

        $task->update($data);
        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
