<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentCredentials;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentCredentialsRequest;
use App\Http\Requests\UpdatePaymentCredentialsRequest;

class PaymentCredentialsController extends Controller
{
    public function socialAdd()
    {
        return view('admin.social-settings.create');
    }

    public function create()
    {
        return view('admin.payment-credentials.create');
    }

    public function store(StorePaymentCredentialsRequest $request)
    {
        foreach ($request->validated() as $key => $value) {
            PaymentCredentials::updateOrCreate(['name' => $key], ['value' => $value]);
        }

        return redirect()->back();
    }

}
