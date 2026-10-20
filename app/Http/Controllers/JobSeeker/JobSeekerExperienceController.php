<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Http\Request;

class JobSeekerExperienceController extends Controller
{
    public function delete($id)
    {
        try {
            $result = WorkExperience::where('id', $id)->delete();
            if ($result) {

                return redirect()->route('job-seeker-profile')->withSuccess('Record deleted successfully');
            } else {
                return redirect()->route('job-seeker-profile')->withError('No matching record found');
            }
        } catch (\Exception $e) {
            return redirect()->route('job-seeker-profile')->withError($e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $countries = getCountries();

        return view('jobseeker.form_element.work_add', compact('countries'));
    }

    public function edit($id)
    {
        $work = WorkExperience::find($id);
        $countries = getCountries();

        return view('jobseeker.form_element.work_edit', compact('work', 'countries'));
    }

    public function store(Request $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;
        try {
            $data = WorkExperience::create($inputs);

            $profileComplete = profile_complete($data->user_id);


            if ($profileComplete == 100) {

                employerMessageReceive($data['user_id'], $data->job_category_id);
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $work = WorkExperience::find($id);
        $inputs = $request->all();
        try {
            $data = $work->update($inputs);

            $profileComplete = profile_complete($work->user_id);
            $jobCategory = User::where('id', $work->user_id)->select('job_category_id')->first();
            if ($profileComplete == 100) {
                employerMessageReceive($work->user_id, $jobCategory->job_category_id);
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
