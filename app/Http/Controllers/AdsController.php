<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ads;
use Illuminate\Support\Facades\Validator;

class AdsController extends Controller
{
    public function Ads(){        
        $arr = Ads::find(1);
        return view('admin.ads.ads', compact('arr'));
    }


    public function adsUpdate(Request $request)
    {   

        $validatedData = $request->validate([
            'square_ads' => 'required',
            'horizontal_ads' => 'required',
            'vertical_ads' => 'required',
        ]);
    
        $input = $validatedData;
        $ads = Ads::first();

        if (!$ads) {
            Ads::create([
                'square_ads' => $input['square_ads'],
                'horizontal_ads' => $input['horizontal_ads'],
                'vertical_ads' => $input['vertical_ads'],
            ]);
            return redirect()->back()->with('success' , "ads Added successfully!");
        } else {
            $ads->square_ads = $input['square_ads'];
            $ads->horizontal_ads = $input['horizontal_ads'];
            $ads->vertical_ads = $input['vertical_ads'];
            $ads->save();
            return redirect()->back()->with('success' , "ads updated successfully!");
        }
    }
}
