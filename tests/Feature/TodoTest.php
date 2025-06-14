<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_user_can_create_todo(): void
    {
        $todoData = [
            'title' => 'Test Todo',
            'description' => 'Test Description',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/todos', $todoData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'user_id',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('todos', [
            'title' => $todoData['title'],
            'description' => $todoData['description'],
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_list_their_todos(): void
    {
        Todo::factory()->count(3)->create(['user_id' => $this->user->id]);
        Todo::factory()->count(2)->create(); // Create todos for other users

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/todos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'page',
                    'limit',
                    'total',
                    'filters',
                ],
            ]);

        $this->assertCount(3, $response->json('data.items'));
    }

    public function test_user_can_update_their_todo(): void
    {
        $todo = Todo::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/todos/{$todo->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Todo updated successfully.',
                'data' => [
                    'id' => $todo->id,
                    'title' => $updateData['title'],
                    'description' => $updateData['description'],
                ],
            ]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => $updateData['title'],
            'description' => $updateData['description'],
        ]);
    }

    public function test_user_cannot_update_other_users_todo(): void
    {
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/todos/{$todo->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden',
            ]);
    }

    public function test_user_can_delete_their_todo(): void
    {
        $todo = Todo::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Todo deleted successfully.',
                'data' => null,
            ]);
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            // deleted_at should not be null
        ]);
        $this->assertNotNull(
            DB::table('todos')->where('id', $todo->id)->value('deleted_at'),
            'Todo should be soft deleted (deleted_at is not null)'
        );
    }

    public function test_user_cannot_delete_other_users_todo(): void
    {
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden',
            ]);
    }

    public function test_todo_list_supports_filtering_and_sorting(): void
    {
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'First Todo',
            'status' => 'pending',
        ]);
        Todo::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Second Todo',
            'status' => 'completed',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/todos?status=completed&sort_by=title&sort_order=asc');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Todos fetched successfully.',
                'data' => [
                    'filters' => [
                        'status' => 'completed',
                        'sort_by' => 'title',
                        'sort_order' => 'asc',
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data.items'));
    }
} 