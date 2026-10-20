<?php

namespace App\Http\Controllers\Admin;

use App\Models\CvDownload;
use ZipArchive;
use App\Models\Job;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\Dashboard\JobCategory;

class CvBankController extends Controller
{
    public function downloadPDFs(Request $request)
    {
        $time = time();
        $inputs = $request->all();
        $pdfFiles = $this->savePDF($inputs, $time);

        $zipFilePath = public_path('zip/'.$time.'/pdf_files.zip');
        $zip = new ZipArchive();
        $zip->open($zipFilePath, ZipArchive::CREATE);

        foreach ($pdfFiles as $pdfFile) {
            $zip->addFile($pdfFile, basename($pdfFile));
        }

        $zip->close();
        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="pdf_files.zip"',
        ];

        try {
            $headers = [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="pdf_files.zip"',
            ];
    
            return response()->file($zipFilePath, $headers)->deleteFileAfterSend(true);;
        } finally {
            $this->makeCvDownloadHistory($request->cv_count);
        }
    }

    public function savePDF($inputs, $time)
    {
        $jobCategory = JobCategory::where('id', $inputs['category_id'])->whereStatus(1)->first();
        $jobs = Job::where('job_category_id', $jobCategory->id)->pluck('id');
        $applications = Application::whereIn('job_id', $jobs)->orderBy('id', 'desc')->take($inputs['cv_count'])->pluck('user_id');
        $users = User::whereIn('id', $applications)->get();
        $pdfFiles = [];

        foreach ($users as $user) {
            // Load user data including relationships
            $jobseeker = User::with([
                'personal_info',
                'work_experiences',
                'trainings',
                'skills',
                'languages',
                'educations.degreeName',
            ])->where('id', $user->id)->first();


            $folder_name = $this->createDirectory($time);

            // Load the PDF view with the user data
            $pdf = PDF::loadView('resume.resume', compact('user'));
            $name = $jobseeker->name . '.pdf';
            $pdfPath = public_path('zip/'.$folder_name."/{$name}");
            $pdf->save($pdfPath);
            $pdfFiles[] = $pdfPath;
        }

        return $pdfFiles;
    }

    public function createDirectory($folder_name)
    {
        $directoryPath = public_path('zip/'.$folder_name);
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0775, true, true);
        }

        return $folder_name;
    }

    public function makeCvDownloadHistory($count){
        $data = CvDownload::create([
            'user_id' => auth()->user()->id,
            'cv_count' => $count
        ]);
        return $data;
    }
}