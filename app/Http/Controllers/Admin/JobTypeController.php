<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobType;
use Illuminate\Http\Request;

class JobTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:job-type.index'])->only('index');
        $this->middleware(['permission:job-type.create'])->only('create');
        $this->middleware(['permission:job-type.edit'])->only('edit');
        $this->middleware(['permission:job-type.delete'])->only('delete');
    }

    public function index()
    {
        $jobTypes = JobType::all();
        return view('admin.job_type.index', compact('jobTypes'));
    }

    public function create()
    {
        $title = "Create Job Type";
        return view('admin.job_type.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:1,0',
        ]);

        $aa = JobType::create($request->all());

        return redirect()->route('job-type.index')->with([
            'message' => 'Data created successfully',
            'class' => 'success',
        ]);
    }

    public function edit($id)
    {
        $title = "Update Job Type";
        $jobType = JobType::findOrFail($id);
        return view('admin.job_type.edit', compact('jobType', 'title'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:1,0',
        ]);

        $jobType = JobType::findOrFail($id);
        $jobType->update($request->all());

        return redirect()->route('job-type.index')->with([
            'message' => 'Data updated successfully',
            'class' => 'success',
        ]);
    }

    public function destroy($id)
    {
        $jobType = JobType::findOrFail($id);
        $jobType->delete();

        return redirect()->route('job-type.index')->with([
            'message' => 'Data deleted successfully',
            'class' => 'success',
        ]);
    }
}
