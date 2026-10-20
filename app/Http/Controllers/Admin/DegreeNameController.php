<?php

namespace App\Http\Controllers\Admin;

use App\Enum\StatusType;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Dashboard\DegreeName;
use App\Http\Requests\StoreDegreeNameRequest;
use App\Http\Requests\UpdateDegreeNameRequest;
use App\Models\Dashboard\DegreeLavel;

class DegreeNameController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:degree-level.index'])->only('index');
        $this->middleware(['permission:degree-level.create'])->only('create');
        $this->middleware(['permission:degree-level.edit'])->only('edit');
        $this->middleware(['permission:degree-level.delete'])->only('delete');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = __('Degree Name');
        $degreeName = DegreeName::with('degreeLavel')->latest()->get();

        return view('admin.degreeName.index', compact('degreeName', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = __('Add Degree Name');
        $degreeLevel = DegreeLavel::where('status', StatusType::ACTIVE)->pluck('name', 'id');

        return view('admin.degreeName.create', compact('title', 'degreeLevel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDegreeNameRequest $request)
    {
        DegreeName::create($request->validated());

        return redirect()->route('degree-name.index')->with([
            'message' => 'degree name added success',
            'class' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DegreeName $degreeName)
    {
        $title = __('Update Degree Name');
        $degreeLevel = DegreeLavel::where('status', StatusType::ACTIVE)->pluck('name', 'id');

        return view('admin.degreeName.edit', compact('title', 'degreeLevel', 'degreeName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeNameRequest $request, DegreeName $degreeName)
    {
        $degreeName->update($request->validated());

        return redirect()->route('degree-name.index')->with([
            'message' => 'degree name added success',
            'class' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DegreeName $degreeName)
    {
        $degreeName->delete();

        return redirect()->back()->with([
            'message' => 'degree name deleted success',
            'class' => 'warning'
        ]);
    }

    public function degreeName($id)
    {
        $degreeName = DegreeName::where('degree_lavel_id', $id)->where('status', StatusType::ACTIVE)->select('id', 'name')->get();

        return response()->json($degreeName);
    }
}
