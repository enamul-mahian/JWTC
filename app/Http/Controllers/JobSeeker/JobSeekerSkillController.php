<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\User;
use App\Models\Skill;
use App\Enum\RoleType;
use Illuminate\Http\Request;
use App\Models\JobseekerSkill;
use App\Mail\EmployerReceiveMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class JobSeekerSkillController extends Controller
{
    public function delete($id)
    {
        try {
            $result = JobseekerSkill::where('id', $id)->delete();
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
        return view('jobseeker.form_element.skill_add');
    }

    public function edit($id)
    {
        $skill = JobseekerSkill::find($id);
        return view('jobseeker.form_element.skill_edit', compact('skill'));
    }

    public function store(Request $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = auth()->user()->id;
        try {
            $data = JobseekerSkill::create($inputs);

            $profileComplete =  profile_complete($data->user_id);

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
        $skill = JobseekerSkill::find($id);

        $inputs = $request->all();
        try {
            $data = $skill->update($inputs);

            $profileComplete =  profile_complete($skill->user_id);

            $jobCategory = User::where('id', $skill['user_id'])->first();

            // return $jobCategory->job_category_id;

            if ($profileComplete == 100) {
                $jobSeeker = User::where('id', $skill['user_id'])->first();

                $users = User::where('job_category_id', $jobCategory->job_category_id)->where('user_type', RoleType::EMPLOYER)->get();

                foreach ($users as $user) {
                    // dispatch(new EmployerReceiveMailJob($user, $jobSeeker));
                    Mail::to($user->email)->send(new EmployerReceiveMail($jobSeeker,  $user));
                }
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
