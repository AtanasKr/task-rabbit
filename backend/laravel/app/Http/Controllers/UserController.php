<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->select('id', 'name', 'email', 'role', 'created_at');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('paginate', true)) {
            $perPage = $request->query('per_page', 10);
            $users = $query->paginate($perPage);
        } else {
            $users = $query->get();
        }

        return response()->json($users, 200);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ], 200);
    }
}
