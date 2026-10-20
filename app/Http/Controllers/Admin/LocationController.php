<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:location.index'])->only('index');
        $this->middleware(['permission:location.create'])->only('create');
        $this->middleware(['permission:location.edit'])->only('edit');
        $this->middleware(['permission:location.delete'])->only('delete');
    }

    public function index()
    {
        $locations = Country::latest()->get();
        
        return view('admin.location.index', compact('locations'));
    }

    public function create()
    {
        $title = "Create Location";

        return view('admin.location.create', compact('title'));
    }

    public function store(Request $request)
    {
        $requestValidated =  $request->validate([
            'name' => 'required|max:255',
            // 'description' => 'nullable',
            'code' => 'required|string|max:255',
            'status' => 'required|in:1,0',
        ]);

        $aa = Country::create( $requestValidated );

        return redirect()->route('location.index')->with([
            'message' => 'Data created successfully',
            'class' => 'success',
        ]);
    }

    public function edit($id)
    {
        $title = "Update Location";
        $location = Country::findOrFail($id);

        return view('admin.location.edit', compact('location', 'title'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            // 'description' => 'nullable',
            'code' => 'required',
            'status' => 'required|in:1,0',
        ]);

        $location = Country::findOrFail($id);
        $location->update($request->all());

        return redirect()->route('location.index')->with([
            'success' => 'location updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $location = Country::findOrFail($id);
        $location->delete();

        return redirect()->route('location.index')->with([
            'warning' => 'Data deleted successfully',
        ]);
    }
}
