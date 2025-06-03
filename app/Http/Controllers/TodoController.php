<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TodoController extends Controller
{
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
            $query->where(function($q) use ($search) {
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

        return response()->json([
            'data' => $todos->items(),
            'page' => $todos->currentPage(),
            'limit' => $todos->perPage(),
            'total' => $todos->total(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $request->user()->id,
            'status' => 'pending', // default status
        ]);

        // Return all fields, including status
        return response()->json($todo->fresh(), 201);
    }

    public function update(Request $request, Todo $todo): JsonResponse
    {
        // Check if the user owns the todo
        if ($todo->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden'
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $todo->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json($todo);
    }

    public function destroy(Request $request, Todo $todo): Response
    {
        // Check if the user owns the todo
        if ($todo->user_id !== $request->user()->id) {
            return response(null, Response::HTTP_FORBIDDEN);
        }

        $todo->delete();

        return response()->noContent();
    }
} 