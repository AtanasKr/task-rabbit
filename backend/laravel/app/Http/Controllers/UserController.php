<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all(['id', 'name', 'email', 'role', 'created_at']);
        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }
}
