<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contact;
use App\Models\User;
use App\Mail\MassEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MassmailController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:sendMassMail'])->only('massMail');
        $this->middleware(['permission:contact-message'])->only('contactMessage');
    }


    public function massMail(){

        return view('dashboard.mass-email');
    }

    public function massMailSend(Request $request){
        $validator = Validator::make($request->all(), [
            'user_type' => 'required',
            'message' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $users = User::where('user_type', $request->user_type)->get();

        foreach ($users as $user) {
            $details = [
                'subject' => 'this is subject',
                'body'      => $request->message,
                'name' => $user->name
            ];

            $mailName = env('MAIL_USERNAME');
            $mailPassword = env('MAIL_PASSWORD');
            if(!empty(env('MAIL_USERNAME') || env('MAIL_PASSWORD'))){
                Mail::to($user->email)->send(new MassEmail($details));
            }else{
                return redirect()->back()->with('warning','Somethings went wrong. please try again!');
            }

            
        }
        return redirect()->route('send-mass-mail')->with('success','Mail sent successfully');
        
    }

    public function contactMessage(){
        $messages = Contact::all();
        return view('admin.dashboard.contact', compact('messages'));
    }
}