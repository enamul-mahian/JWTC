<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\JobCategory;
use App\Http\Requests\StoreJobCategoryRequest;
use App\Http\Requests\UpdateJobCategoryRequest;
use App\Models\UniqueCode;
use Illuminate\Http\Request;

class JobCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:job-categories.index'])->only('index');
        $this->middleware(['permission:job-categories.create'])->only('create');
        $this->middleware(['permission:job-categories.edit'])->only('edit');
        $this->middleware(['permission:job-categories.delete'])->only('delete');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $this->authorize('permission', 'job-categories.index');

        $title = __('Job categories');
        $jobCategories = JobCategory::latest()->get();

        return view('admin.jobCategory.index', compact('jobCategories', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $title = __('Create Job category');

        return view('admin.jobCategory.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobCategoryRequest $request)
    {
        JobCategory::create($request->validated());

        return redirect()->route('job-categories.index')->with([
            'success' => 'Job category create success',
            'class' => 'success',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobCategory $jobCategory)
    {

        $title = __('Update Job category');

        return view('admin.jobCategory.edit', compact('jobCategory', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobCategoryRequest $request, JobCategory $jobCategory)
    {
        $jobCategory->update($request->validated());

        return redirect()->route('job-categories.index')->with([
            'message' => 'Job category update success',
            'class' => 'success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobCategory $jobCategory)
    {
        $jobCategory->delete();

        return redirect()->back()->with([
            'message' => 'Job category delete success',
            'class' => 'warning',
        ]);
    }


    //Unique codes...................
    public function allUniqueCodeList()
    {
        $datas = UniqueCode::get();

        return view('admin.uniquecode.index', compact('datas'));
    }

    public function codeRequestList()
    {
        $datas = UniqueCode::where('is_used', 0)->where('code', null)->get();

        return view('admin.uniquecode.request', compact('datas'));
    }

    public function updateCode(Request $request)
    {
        $uniqueCode = UniqueCode::findOrFail($request->unique_code_id);
        $uniqueCode->update(['code' => $request->code]);

        return redirect()->route('code-request')->with([
            'message' => 'Code Updated Successfully.',
            'class' => 'success',
        ]);
    }
}
