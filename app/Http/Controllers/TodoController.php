<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Services\TodoService;

class TodoController extends Controller
{
    protected TodoService $todoService;

    public function __construct(TodoService $todoService)
    {
        $this->todoService = $todoService;
    }

    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $search = $request->input('search', '');
        $status = $request->input('status');

        $query = Todo::where('user_id', $request->user()->id);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply sorting
        $allowedSortColumns = ['created_at', 'updated_at', 'title', 'status'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        $todos = $query->orderBy($sortBy, $sortOrder)
            ->paginate($limit, ['*'], 'page', $page);

        $data = [
            'items' => $todos->items(),
            'page' => $todos->currentPage(),
            'limit' => $todos->perPage(),
            'total' => $todos->total(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ];

        return apiResponse($data, 'Todos fetched successfully.');
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $todo = $this->todoService->create($validated, $request->user());
        return apiResponse($todo->fresh(), 'Todo created successfully.', true, 201);
    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            return apiResponse(null, 'Forbidden', false, Response::HTTP_FORBIDDEN);
        }
        $validated = $request->validated();
        $todo = $this->todoService->update($todo, $validated);
        return apiResponse($todo, 'Todo updated successfully.');
    }

    public function destroy(Request $request, Todo $todo): JsonResponse
    {
        if ($todo->user_id !== $request->user()->id) {
            return apiResponse(null, 'Forbidden', false, Response::HTTP_FORBIDDEN);
        }
        $this->todoService->delete($todo);
        return apiResponse(null, 'Todo deleted successfully.', true, 200);
    }


}
