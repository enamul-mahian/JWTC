<?php

namespace App\Http\Controllers\Employer;

use App\Enum\StatusType;
use App\Models\User;
use App\Models\EmployerInformation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreEmployerInformationRequest;
use App\Http\Requests\UpdateEmployerInformationRequest;
use App\Models\Dashboard\JobCategory;

class EmployerInformationController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $info = EmployerInformation::where('user_id', $id)->first();
        $user = User::where('id', Auth::user()->id)->first();
        $jobCategory = JobCategory::where('status', StatusType::ACTIVE)->select('id', 'name')->latest()->get();

        return view('frontend.pages.employer.update_profile', compact('info', 'jobCategory', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployerInformationRequest $request, $id)
    {
        $information = EmployerInformation::where('user_id', $id)->first();
        $data = $request->except('job_category_id');

        $data['user_id'] = $id;

        if ($request->hasFile('photo')) {
            $old = $information->photo ?? null;

            $image = $request->file('photo');
            $photo = fileUploader($image, getFilePath('profilephoto'), getFileSize('profilephoto'), $old);
            $data['photo'] = $photo;
        }

        $matchThese = ['user_id' => $id];
        EmployerInformation::updateOrCreate($matchThese, $data);

        User::where('id', Auth::user()->id)->update(['job_category_id' => $request->job_category_id]);

        return redirect()->back()->withSuccess('profile updated success');
    }
}
