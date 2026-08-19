<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = array_map(
            fn (Role $role) => $role->value,
            Role::cases()
        );

        return response()->json([
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::enum(Role::class),
            ],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'message' => 'Role modifie avec succes',
            'user' => $user,
        ]);
    }
}