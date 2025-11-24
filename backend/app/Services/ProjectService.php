<?php

namespace App\Services;

use App\Models\Project;

class ProjectService
{
    public function getProjects(array $filters, $user)
    {
        $query = Project::with('members:id,name,email');

        if ($user->role !== 'admin') {
            $query->whereHas(
                'members',
                fn($q) =>
                $q->where('users.id', $user->id)
            );
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if (!empty($filters['paginate']) && $filters['paginate'] == true) {
            return $query->paginate($filters['per_page'] ?? 10);
        }

        return $query->get();
    }

    public function authorizeAccess(Project $project, $user)
    {
        if ($user->role === 'admin') {
            return;
        }

        $isMember = $project->members()->where('user_id', $user->id)->exists();

        if (!$isMember) {
            abort(403, 'Unauthorized');
        }
    }

    public function addMembers(Project $project, array $userIds)
    {
        $project->members()->syncWithoutDetaching($userIds);

        return $project->members()->get(['users.id', 'users.name', 'users.email']);
    }

    public function removeMembers(Project $project, array $userIds)
    {
        $project->members()->detach($userIds);

        return $project->members()->get(['users.id', 'users.name', 'users.email']);
    }
}
