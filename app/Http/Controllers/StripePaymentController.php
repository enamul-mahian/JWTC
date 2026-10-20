<?php

namespace App\Http\Controllers;

use Stripe;
use Session;
use Throwable;
use Illuminate\Http\Request;
use App\Models\UserPaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;

class StripePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment($id)
    {
        $plan = Plan::findOrFail($id);
        // $cardInfo = UserPaymentMethod::where('user_id', Auth::user()->id)->first();

        // if (empty($cardInfo)) {
        //     return redirect()->route('buyer-payment-methods.create')->with('waiting', 'card add first.');
        // }

        return view('frontend.pages.employer.payment', compact('plan', 'cardInfo'));
    }

    public function stripe($id)
    {
        return view('frontend.pages.employer.payment', [
                    'plan' => Plan::findOrFail($id),
                ]);
    }

    public function stripePost(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $subscription_id = Subscription::where('subscriber_id', Auth::user()->id)->first();

            $plan = Plan::findOrFail($id);

            if (!empty($subscription_id)) {
                $subscription = Subscription::find($subscription_id->id);
            }

            $user = Auth::user();
            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            Stripe\Charge::create([
                "amount" => $plan->price * 100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Payment Done."
            ]);

            if (!empty($subscription_id)) {
                $subscription->changePlan($plan);
            } else {
                $user->newPlanSubscription('main', $plan);
            }

            Session::flash('success', 'Payment successful!');

            DB::commit();
            return redirect()->route('employer.dashboard')->with('success', 'Payment successful!');
        } catch (Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withError('errors', $th->getMessage());
        }
    }
}
