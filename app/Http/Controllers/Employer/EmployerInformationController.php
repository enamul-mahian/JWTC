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
        abort_unless((int) $id === (int) Auth::id(), 403);
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
        abort_unless((int) $id === (int) Auth::id(), 403);
        $request->validate([
            'photo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'job_category_id' => ['sometimes', 'integer', 'exists:job_categories,id'],
        ]);
        $information = EmployerInformation::where('user_id', $id)->first();
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('photo')) {
            $old = $information->photo ?? null;

            $image = $request->file('photo');
            $photo = fileUploader($image, getFilePath('profilephoto'), getFileSize('profilephoto'), $old);
            $data['photo'] = $photo;
        }

        $matchThese = ['user_id' => $id];
        EmployerInformation::updateOrCreate($matchThese, $data);

        if ($request->filled('job_category_id')) {
            User::whereKey(Auth::id())->update(['job_category_id' => $request->input('job_category_id')]);
        }

        return redirect()->back()->withSuccess('profile updated success');
    }
}
