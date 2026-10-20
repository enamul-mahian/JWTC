<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySize;
use Illuminate\Http\Request;
use Validator;

class CompanySizeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:company-size.index'])->only('index');
        $this->middleware(['permission:company-size.create'])->only('create');
        $this->middleware(['permission:company-size.edit'])->only('edit');
        $this->middleware(['permission:company-size.delete'])->only('delete');
    }


    public function index()
    {
        $companySizes = CompanySize::all();

        return view('admin.company_size.index', compact('companySizes'));
    }

    public function create()
    {
        $title = "Create Company Size";
        return view('admin.company_size.create', compact('title'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'status' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        CompanySize::create($request->all());

        return redirect()->route('company-size.index')->with([
            'message' => 'Data created successfully',
            'class' => 'success',
        ]);
    }

    public function edit($id)
    {
        $title = "Update Company Size";
        $companySize = CompanySize::findOrFail($id);
        return view('admin.company_size.edit', compact('companySize', 'title'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'status' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $companySize = CompanySize::findOrFail($id);
        $companySize->update($request->all());

        return redirect()->route('company-size.index')->with([
            'message' => 'Data updated successfully',
            'class' => 'success',
        ]);
    }

    public function destroy($id)
    {
        $companySize = CompanySize::findOrFail($id);
        $companySize->delete();

        return redirect()->route('company-size.index')->with([
            'message' => 'Data deleted successfully',
            'class' => 'success',
        ]);
    }
}
