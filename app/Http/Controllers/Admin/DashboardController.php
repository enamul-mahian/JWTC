<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('dashboard.index');
    }

    public function index()
    {
        $user = User::all();

        return view('admin.userManagement.index', compact('user'));
    }

    public function uploadPhoto(Request $request)
    {
        try {
            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                $photo = fileUploader($image, getFilePath('profilephoto'), getFileSize('profilephoto'));
                $user = auth()->user();
                $user->photo = $photo;
                $user->save();
            }
            return response()->json(['success' => 'form submitted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function userPermission()
    {
        $role = Role::all();

        return view('admin.userManagement.permission', compact('role'));
    }

    public function userCreate()
    {
        $role = Role::all();

        return view('admin.userCreate.create', compact('role'));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|string',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|max:255|string',
            'role' => 'required|max:255|string',
        ]);

        // return $request->all();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->input('phone') ?: null,
            'password' => Hash::make($request->password),
            'user_type' => $request->role,
        ]);

        $role = Role::where('name', $request->role)->first();

        $user->roles()->attach($role->id);

        return redirect()->route('user-management.index')->withSuccess('user created successfully');
    }

    public function userEdit($id)
    {

        $user = User::findOrFail($id);
        $roles = $user->getRoleNames();

        $role = Role::all();

        return view('admin.userCreate.edit', compact('role', 'user', 'roles'));
    }

    public function userRoleUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|string',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|max:255|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Persist only validated profile fields, not arbitrary submitted
        // attributes such as passwords, account flags, or model columns.
        $data = $validator->validated();
        $role = Role::where('name', $data['role'])->first();
        if (!$role) {
            return back()->withErrors(['role' => 'Select a valid role.'])->withInput();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $data, $role) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);
            $user->syncRoles([$role]);
        });

        return redirect()->route('user-management.index')->withSuccess('User updated successfully');
    }
}
