<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PdfController extends Controller
{
    public function generatePDF()
    {
        $user = User::with(
            ['personal_info','work_experiences', 'trainings', 'skills', 'languages', 'educations']
        )->where('id', auth()->user()->id)->first();
        
        $pdf = PDF::loadView('resume.resume', compact('user'));
        
        return $pdf->stream('my_pdf.pdf');
    }
}