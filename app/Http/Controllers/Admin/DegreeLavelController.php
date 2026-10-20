<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\DegreeLavel;
use App\Http\Requests\StoreDegreeLavelRequest;
use App\Http\Requests\UpdateDegreeLavelRequest;

class DegreeLavelController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:degree-name.index'])->only('index');
        $this->middleware(['permission:degree-name.create'])->only('create');
        $this->middleware(['permission:degree-name.edit'])->only('edit');
        $this->middleware(['permission:degree-name.delete'])->only('delete');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title =  __('Degree Level List');
        $degree = DegreeLavel::latest()->get();

        return view('admin.degreeLevel.index', compact('degree', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title =  __('Add Degree Level');

        return view('admin.degreeLevel.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDegreeLavelRequest $request)
    {
        DegreeLavel::create($request->validated());

        return redirect()->route('degree-level.index')->with([
            'message' => 'degree added success',
            'class' => 'success'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $title =  __('Update Degree Level');
        $degreeLavel = DegreeLavel::findOrFail($id);

        return view('admin.degreeLevel.edit', compact('degreeLavel', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeLavelRequest $request,  $id)
    {
        $degreeLavel = DegreeLavel::findOrFail($id);
        $degreeLavel->update($request->validated());

        return redirect()->route('degree-level.index')->with([
            'message' => 'degree level update success',
            'class' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $degreeLavel = DegreeLavel::findOrFail($id);
        $degreeLavel->delete();

        return redirect()->back()->with([
            'message' => 'degree deleted success',
            'class' => 'warning'
        ]);
    }
}
