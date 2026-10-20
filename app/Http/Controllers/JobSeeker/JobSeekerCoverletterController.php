<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\CoverLetter;
use Illuminate\Http\Request;

class JobSeekerCoverletterController extends Controller
{
    public function coverLetter(){
        $cover= CoverLetter::where('user_id', auth()->user()->id)->first();
        
        return view('jobseeker.cover.cover_letter', compact('cover'));
    }

    public function savePersonInfo(Request $request){
        try {
            $matchThese = ['user_id'=> auth()->user()->id];
            $inputs = $request->all();
            $inputs['user_id'] = auth()->user()->id;
            $data = CoverLetter::updateOrCreate($matchThese,$inputs);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    
    public function saveContactInfo(Request $request){
        try {
            $inputs = $request->all();
            $matchThese = ['user_id'=> auth()->user()->id];
            $inputs['user_id'] = auth()->user()->id;
            $data = CoverLetter::updateOrCreate($matchThese,$inputs);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    
    public function saveContentInfo(Request $request){
        try {
            $matchThese = ['user_id'=> auth()->user()->id];
            $inputs = $request->all();
            
            $inputs['user_id'] = auth()->user()->id;
            $data = CoverLetter::updateOrCreate($matchThese,$inputs);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    
    public function saveClosingInfo(Request $request){
        try {
            $inputs = $request->all();
            if ($request->hasFile('signature')) {
                $image = $request->file('signature');
                $photo = fileUploader($image, getFilePath('signature'), null);
                $inputs['signature'] = $photo;
            }
            $matchThese = ['user_id'=> auth()->user()->id];
            $inputs['user_id'] = auth()->user()->id;
            $data = CoverLetter::updateOrCreate($matchThese,$inputs);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}