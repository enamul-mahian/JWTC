<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function contact(){
        return view('frontend.pages.contact');
    }

    public function contactStore(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        Contact::create($request->all());

        return redirect()->route('contact')->with('success', 'Message sent successfully!');
    }
}