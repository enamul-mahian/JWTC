<?php

namespace App\Http\Controllers\API;

use App\Enum\StatusType;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function registerEmployer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'job_categories_id' => ['required', 'integer', 'exists:job_categories,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $role = Role::where('name', 'Employer')->where('guard_name', 'web')->first();
        if (!$role) {
            return response()->json(['message' => 'Employer registration is temporarily unavailable.'], 503);
        }

        $user = DB::transaction(function () use ($data, $role) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'],
                'job_category_id' => $data['job_categories_id'],
                'user_type' => UserType::EMPLOYER,
                'status' => StatusType::ACTIVE,
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole($role);
            return $user;
        });

        $token = $user->createToken('jwtc-api')->plainTextToken;

        return response()->json([
            'message' => 'Employer registered.',
            'user' => $user->only(['id', 'name', 'email', 'user_type']),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || (int) $user->status !== StatusType::ACTIVE ||
            !Hash::check($data['password'], (string) $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'user_type']),
            'token' => $user->createToken('jwtc-api')->plainTextToken,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password updated. Please sign in again.',
        ]);
    }
}
