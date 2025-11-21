<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('members:id,name,email');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->boolean('paginate', true)) {
            $perPage = $request->query('per_page', 10);
            $projects = $query->paginate($perPage);
        } else {
            $projects = $query->get();
        }

        return response()->json($projects, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        $project->load('members:id,name,email');
        return response()->json($project, 200);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $project->update($validated);

        return response()->json($project, 200);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ], 200);
    }

    public function addMembers(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $project->members()->syncWithoutDetaching($validated['user_ids']);

        $members = $project->members()->get([
            'users.id', 'users.name', 'users.email'
        ]);

        return response()->json([
            'message' => 'Users added successfully',
            'members' => $members
        ], 200);
    }

    public function removeMembers(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $project->members()->detach($validated['user_ids']);

        $members = $project->members()->get([
            'users.id', 'users.name', 'users.email'
        ]);

        return response()->json([
            'message' => 'Users removed successfully',
            'members' => $members
        ], 200);
    }
}
