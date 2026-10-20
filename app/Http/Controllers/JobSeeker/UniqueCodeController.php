<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\UniqueCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UniqueCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $canCreate = UniqueCode::where('user_id', auth()->user()->id)->where('is_used', 0)->where('code', '!=', null)->count();
        $canCreate = checkAvailability();
        $uniqueCodes = UniqueCode::with('user')->where('user_id', auth()->user()->id)->get();
        return view('jobseeker.application.uniquecode', compact('uniqueCodes', 'canCreate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $canCreate = checkAvailability();
        if ($canCreate == 0) {
            return redirect()->route('unique_codes')->withErrors('Operation Failed!');
        }

        //Send code request
        UniqueCode::create([
            'user_id' => auth()->user()->id,
            'code' => null,
            'date' => date('Y-m-d'),
            'is_used' => 0,
        ]);

        // Redirect with a success message
        return redirect()->route('unique_codes')->withSuccess('Request Sent!');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UniqueCode $uniqueCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UniqueCode $uniqueCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UniqueCode $uniqueCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UniqueCode $uniqueCode)
    {
        //
    }
}
