<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;

class JobSeekerTrainingController extends Controller
{
    public function delete($id)
    {
        try {
            $result = Training::where('id', $id)->delete();
            if ($result) {
                return back()->withSuccess('Record deleted successfully');
            } else {
                return back()->withError('No matching record found');
            }
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $countries = getCountries();
        return view('jobseeker.form_element.training_add', compact('countries'));
    }

    public function edit($id)
    {
        $countries = getCountries();
        $training = Training::find($id);
        return view('jobseeker.form_element.training_edit', compact('training', 'countries'));
    }

    public function store(Request $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;

        try {
            $data = Training::create($inputs);

            $profileComplete =  profile_complete($inputs['user_id']);
            $jobCategory = User::where('id', $inputs['user_id'])->select('job_category_id')->first();

            if ($profileComplete == 100) {
                employerMessageReceive($inputs['user_id'], $jobCategory->job_category_id);
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $training = Training::find($id);
        $inputs = $request->all();
        try {
            $data = $training->update($inputs);
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
