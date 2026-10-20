<?php

namespace App\Http\Controllers\JobSeeker;

use App\Enum\StatusType;
use App\Models\Education;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dashboard\DegreeName;
use App\Models\Dashboard\DegreeLavel;
use App\Models\User;

class JobSeekerEducationController extends Controller
{
    public function delete($id)
    {
        try {
            $result = Education::where('id', $id)->delete();
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
        $education_levels = DegreeLavel::whereStatus(StatusType::ACTIVE)->get();
        return view('jobseeker.form_element.education_add', compact('education_levels'));
    }

    public function edit($id)
    {
        $education = Education::find($id);
        $education_levels = DegreeLavel::whereStatus(StatusType::ACTIVE)->get();
        return view('jobseeker.form_element.education_edit', compact('education', 'education_levels'));
    }

    public function store(Request $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;
        try {
            $data = Education::create($inputs);

            $profileComplete =  profile_complete($inputs['user_id']);

            $userJobCategory = User::where('id', $inputs['user_id'] )->select('job_category_id')->first();

            if ($profileComplete == 100) {
                employerMessageReceive($inputs['user_id'] , $userJobCategory->job_category_id);
            }
            
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $education = Education::find($id);

        $inputs = $request->all();
        try {
            $data = $education->update($inputs);

            $profileComplete =  profile_complete($education->user_id);
            $userJobCategory = User::where('id', $education->user_id)->select('job_category_id')->first();

            if ($profileComplete == 100) {
                employerMessageReceive($education->user_id, $userJobCategory->job_category_id);
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getGegreeName($level_id)
    {
        $degree_names = DegreeName::where('degree_lavel_id', $level_id)->get();

        return response()->json($degree_names);
    }
}
