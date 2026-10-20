<?php

namespace App\Http\Controllers\Admin;

use App\Models\FrontPage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FrontPageController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:page.index'])->only('index');
        $this->middleware(['permission:page.create'])->only('getInActiveJobSeeker');
        $this->middleware(['permission:page.edit'])->only('edit');
        $this->middleware(['permission:page.delete'])->only('delete');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = FrontPage::all();
        
        return view('admin.frontpage.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.frontpage.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'url' => 'required|string|max:255',
                'content' => 'nullable|string',
                'order_by' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return redirect()->route('pages.index')->with([
                    'message' => $validator->errors(),
                    'class' => 'error',
                ]);
            }

            $url = Str::slug($request->input('url'));

            $frontPage = new FrontPage([
                'name' => $request->input('name'),
                'url' => $url,
                'content' => $request->input('content'),
                'order_by' => $request->input('order_by'),
            ]);

            $frontPage->save();
            return redirect()->route('pages.index')->with([
                'message' => 'Data created successfully',
                'class' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pages.index')->with([
                'message' => $e->getMessage(),
                'class' => 'error',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $page = FrontPage::find($id);
        return view('admin.frontpage.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'url' => 'required|string|max:255',
                'content' => 'nullable|string',
                'order_by' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return redirect()->route('pages.edit', $id)->with([
                    'message' => $validator->errors(),
                    'class' => 'error',
                ]);
            }

            // Find the FrontPage record by ID
            $frontPage = FrontPage::findOrFail($id);

            // Update the record with the validated data
            $frontPage->update([
                'name' => $request->input('name'),
                'url' => Str::slug($request->input('url')),
                'content' => $request->input('content'),
                'order_by' => $request->input('order_by'),
            ]);

            return redirect()->route('pages.index')->with([
                'message' => 'Data updated successfully',
                'class' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pages.edit', $id)->with([
                'message' => $e->getMessage(),
                'class' => 'error',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $page = FrontPage::find($id);
        $page->delete();
        return redirect()->route('pages.index')->with([
            'success' => 'Data deleted successfully',
            'class' => 'success',
        ]);
    }

    public function uploadEditorFile(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
            $request->file('upload')->move(public_path('media'), $fileName);
            $url = asset('media/' . $fileName);
            return new JsonResponse(['fileName' => $fileName, 'uploaded'=> 1, 'url' => $url]);
        }
    }
}