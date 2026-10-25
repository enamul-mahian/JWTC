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
        // The contacts table stores 255-character strings. Validate before
        // SQL and persist only known public-form fields (never arbitrary input).
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
        ]);

        Contact::create($data);

        return redirect()->route('contact')->with('success', 'Your message has been received.');
    }
}