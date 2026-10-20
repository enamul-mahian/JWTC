<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoverLetter;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class WordController extends Controller
{
    public function exportToWord()
    {
        $data = CoverLetter::where('user_id', auth()->user()->id)->first();
        $htmlContent = view('word.cover_letter', compact('data'))->render();
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText(html_entity_decode(strip_tags($htmlContent)), null, array('alignment' => Jc::BOTH));
        $filename = 'document_' . auth()->user()->id . '_' . time() . '.docx';
        $publicPath = public_path('assets/images/cover/' . $filename);
        $phpWord->save($publicPath);
        $data->cover_letter = $filename;
        $data->save();
        return response()->download($publicPath);
    }

    public function exportToPDF()
    {
        $user = User::with(
            ['cover_letter']
        )->where('id', auth()->user()->id)->first();
        $pdf = PDF::loadView('jobseeker.cover.cover_pdf', compact('user'));
        return $pdf->stream('my_pdf.pdf');
    }
}
