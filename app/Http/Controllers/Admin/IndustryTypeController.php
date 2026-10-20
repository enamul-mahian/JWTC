<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndustryType;
use Illuminate\Http\Request;

class IndustryTypeController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:industry-type.index'])->only('index');
        $this->middleware(['permission:industry-type.create'])->only('create');
        $this->middleware(['permission:industry-type.edit'])->only('edit');
        $this->middleware(['permission:industry-type.delete'])->only('delete');
    }


    public function index()
    {
        $industryTypes = IndustryType::latest()->get();
        
        return view('admin.industry_type.index', compact('industryTypes'));
    }

    public function create()
    {
        $title = "Create Industry Type";
        return view('admin.industry_type.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:255',
            'status' => 'required|in:1,0',
        ]);

        $aa = IndustryType::create($request->all());

        return redirect()->route('industry-type.index')->with([
            'message' => 'Data created successfully',
            'class' => 'success',
        ]);
    }

    public function edit($id)
    {
        $title = "Update Industry Type";
        $industryType = IndustryType::findOrFail($id);
        return view('admin.industry_type.edit', compact('industryType', 'title'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:1,0',
        ]);

        $industryType = IndustryType::findOrFail($id);
        $industryType->update($request->all());

        return redirect()->route('industry-type.index')->with([
            'message' => 'Data updated successfully',
            'class' => 'success',
        ]);
    }

    public function destroy($id)
    {
        $industryType = IndustryType::findOrFail($id);
        $industryType->delete();

        return redirect()->route('industry-type.index')->with([
            'message' => 'Data deleted successfully',
            'class' => 'success',
        ]);
    }
}
