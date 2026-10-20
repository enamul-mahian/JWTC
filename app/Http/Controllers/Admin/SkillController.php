<?php

namespace App\Http\Controllers\Admin;

use App\Enum\StatusType;
use App\Models\Skill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\storeSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Models\Dashboard\JobCategory;

class SkillController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:skills.index'])->only('index');
        $this->middleware(['permission:skills.create'])->only('create');
        $this->middleware(['permission:skills.edit'])->only('edit');
        $this->middleware(['permission:skills.delete'])->only('delete');
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $skills = Skill::with('jobCategory')->latest()->get();

        return view('admin.skill.index', compact('skills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Add Skill';

        $jobCategories = JobCategory::select('id', 'name')->where('status', StatusType::ACTIVE)->get();

        return view('admin.skill.create', compact('jobCategories', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(storeSkillRequest $request)
    {
        Skill::create($request->validated());

        return redirect()->route('skills.index')->withSuccess('skills added success');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $title = 'Add Skill';
        $skill = Skill::findOrFail($id);
        $jobCategories = JobCategory::select('id', 'name')->where('status', StatusType::ACTIVE)->get();

        return view('admin.skill.edit', compact('jobCategories', 'title', 'skill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSkillRequest $request, Skill $skill)
    {
        $skill->update($request->validated());

        return redirect()->route('skills.index')->withSuccess('skills updated success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill)
    {
        $skill->delete();

        return redirect()->back()->withWarning('skills deleted success');
    }
}
