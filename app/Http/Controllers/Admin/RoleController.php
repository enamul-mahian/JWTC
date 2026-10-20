<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Role::with('permissions')->latest()->get();

        return view('admin.rolepermisson.index', compact('role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permission = Permission::role('admin')->latest()->get();

        return view('admin.rolepermisson.create', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name,',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        $role->syncPermissions($request->permision);
        return redirect()->route('user-permission.index')->withSuccess('user role created successfully');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permission = Permission::role('admin')->latest()->get();

        $data = $role->permissions()->pluck('id')->toArray();

        return view('admin.rolepermisson.edit', compact('role', 'permission', 'data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permision);

        return redirect()->route('user-permission.index')->withSuccess('user role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->back()->with([
            'msg' => 'role deleted success',
            'cls' => 'warning'
        ]);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permission = Permission::role('admin')->latest()->get();

        $data = $role->permissions()->pluck('id')->toArray();

        return view('admin.rolepermisson.show', compact('role', 'permission', 'data'));
    }
}
