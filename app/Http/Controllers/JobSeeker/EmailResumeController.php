<?php

namespace App\Http\Controllers\JobSeeker;

use App\Mail\ResumeMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailResumeController extends Controller
{
    public function index(){
        return view('jobseeker.dashboard.email_resume');
    }

    public function sendResume(Request $request){

        $validator = Validator::make($request->all(), [
            'txtMailSubject' => 'required|string|max:255',
            'txtApplication' => 'required|string|max:255',
            'txtCompanyEmail' => 'required|email|max:255',
            'attachment' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $to = $request->input('txtCompanyEmail');
            $subject = $request->input('txtMailSubject');
            $message = $request->input('txtApplication');
            $name = $request->input('txtUserEmail');

            if ($request->hasFile('attachment')) {
                $image = $request->file('attachment');
                $photo = fileUploader($image, getFilePath('attachment'), null);
                $file = $photo;
            }else{
                $file = null;
            }

            sendNotification($userId=2, $title='test', $url='/', $action='click', $outro='end');

            $details = [
                'subject' => $subject,
                'message' => $message,
                'name' => $name,
                'attachment' => $file
            ];

            Mail::to($to)->send(new ResumeMail($details));

            return response()->json(['success' => 'Email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}