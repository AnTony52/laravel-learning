<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Guest task',
            'description' => 'Should fail',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_only_sees_their_own_tasks(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::create([
            'user_id' => $owner->id,
            'title' => 'Owner task',
            'description' => 'Owner only',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        Task::create([
            'user_id' => $otherUser->id,
            'title' => 'Other task',
            'description' => 'Other user',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Owner task']);
        $response->assertJsonMissing(['title' => 'Other task']);
    }

    public function test_user_cannot_access_other_users_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::create([
            'user_id' => $owner->id,
            'title' => 'Private task',
            'description' => 'No access',
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/tasks/{$task->id}")->assertStatus(403);
        $this->putJson("/api/tasks/{$task->id}", ['title' => 'Hacked'])->assertStatus(403);
        $this->deleteJson("/api/tasks/{$task->id}")->assertStatus(403);
    }

    public function test_create_task_assigns_authenticated_user(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Owned task',
            'description' => 'Created by owner',
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Owned task',
            'user_id' => $user->id,
        ]);
    }
}
