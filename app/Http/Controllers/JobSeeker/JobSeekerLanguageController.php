<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;

class JobSeekerLanguageController extends Controller
{
    public function delete($id)
    {
        try {
            $result = Language::where('id', $id)->delete();
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
        return view('jobseeker.form_element.language_add');
    }

    public function edit($id)
    {
        $lang = Language::find($id);
        return view('jobseeker.form_element.language_edit', compact('lang'));
    }

    public function store(Request $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;
        try {
            $data = Language::create($inputs);

            $profileComplete =  profile_complete($data->user_id);
            $userJobCategory = User::where('id', $inputs['user_id'])->select('job_category_id')->first();

            if ($profileComplete == 100) {
                employerMessageReceive($inputs['user_id'], $userJobCategory->job_category_id);
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $lang = Language::find($id);
        $inputs = $request->all();
        try {
            $data = $lang->update($inputs);

            $profileComplete =  profile_complete($lang->user_id);

            $userJobCategory = User::where('id', $lang['user_id'])->select('job_category_id')->first();

            if ($profileComplete == 100) {
                employerMessageReceive($lang['user_id'], $userJobCategory->job_category_id);
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
