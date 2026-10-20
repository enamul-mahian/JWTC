<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\UploadedCv;
use Illuminate\Http\Request;
use App\Models\JobseekerUploads;
use App\Http\Controllers\Controller;
use App\Models\JobseekerCertificate;

class JobseekerImportController extends Controller
{
    public function inport() {
        return view('jobseeker.import.index');
    }
    
    public function certificate() {
        return view('jobseeker.import.certificate');
    }
    
    public function jobseekerUpload() {
        return view('jobseeker.import.upload');
    }

    public function store(Request $request){
        $request->validate([
            'resume' => 'required|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480',
        ]);

        try {
            if ($request->hasFile('resume')) {
                $image = $request->file('resume');
                $photo = fileUploader($image, getFilePath('resume'), null);
                $originalName = $image->getClientOriginalName();
                $uploadedCv = UploadedCv::create([
                    'user_id' => auth()->user()->id,
                    'file' => $photo,
                    'title'=> $originalName,
                ]);

            }
            return redirect()->route('library')->withSuccess('Cv uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('library')->withError($e->getMessage());
        }
    
    }
    
    public function storeCertificate(Request $request){
        $request->validate([
            'certificate' => 'required|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480',
        ]);

        try {
            if ($request->hasFile('certificate')) {
                $image = $request->file('certificate');
                $originalName = $image->getClientOriginalName();
                $photo = fileUploader($image, getFilePath('certificate'), null);
                $uploadedCv = JobseekerCertificate::create([
                    'user_id' => auth()->user()->id,
                    'file' => $photo,
                    'title' => $originalName
                ]);
            }
            return redirect()->route('library')->withSuccess('Certificate uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('library')->withError($e->getMessage());
        }
    
    }
    
    public function jobseekerDocUpload(Request $request){
        $request->validate([
            'upload' => 'required|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480',
        ]);

        try {
            if ($request->hasFile('upload')) {
                $image = $request->file('upload');
                $originalName = $image->getClientOriginalName();
                $photo = fileUploader($image, getFilePath('document'), null);
                $uploadedCv = JobseekerUploads::create([
                    'user_id' => auth()->user()->id,
                    'file' => $photo,
                    'title' => $originalName
                ]);
            }
            return redirect()->route('library')->withSuccess('File uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->route('library')->withError($e->getMessage());
        }
    
    }

    public function cvDelete($id){
        try {
            $result = UploadedCv::where('id', $id)->delete();
            if($result) {
                return redirect()->route('job-seeker-profile')->withSuccess('Record deleted successfully');
            } else {
                return redirect()->route('job-seeker-profile')->withError('No matching record found');
            }
        } catch (\Exception $e) {
            return redirect()->route('job-seeker-profile')->withError($e->getMessage());
        }
    }
    
    public function certificateDelete($id){
        try {
            $result = JobseekerCertificate::where('id', $id)->delete();
            if($result) {
                return redirect()->route('job-seeker-profile')->withSuccess('Record deleted successfully');
            } else {
                return redirect()->route('job-seeker-profile')->withError('No matching record found');
            }
        } catch (\Exception $e) {
            return redirect()->route('job-seeker-profile')->withError($e->getMessage());
        }
    }
    
    public function docsDelete($id){
        try {
            $result = JobseekerUploads::where('id', $id)->delete();
            if($result) {
                return redirect()->route('job-seeker-profile')->withSuccess('Record deleted successfully');
            } else {
                return redirect()->route('job-seeker-profile')->withError('No matching record found');
            }
        } catch (\Exception $e) {
            return redirect()->route('job-seeker-profile')->withError($e->getMessage());
        }
    }

}