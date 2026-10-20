<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use Illuminate\Support\Facades\DB;
use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (!Auth::user()->can('plan.list')) {
        //     abort(403);
        // }

        $plans = Plan::latest()->get();

        return view('admin.plan.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // if (!Auth::user()->can('plan.create')) {
        //     abort(403);
        // }

        return view('admin.plan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlanRequest $request)
    {
        // if (!Auth::user()->can('plan.create')) {
        //     abort(403);
        // }

        try {
            $plan = Plan::create($request->validated());

            foreach ($request->features as $featureData) {

                $feature = [
                    'plan_id' => $plan->id,
                    'name' => $featureData['name'],
                    'value' => $featureData['value'],
                    'features_type' => $featureData['features_type'],
                    'sort_order' => $featureData['sort_order'],
                ];
                Feature::create($feature);
            }

            return redirect()->route('plans.index')->with('success', 'Plan create successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {

        return view('admin.plan.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        // if (!Auth::user()->can('plan.edit')) {
        //     abort(403);
        // }

        $features = $plan->features;

        return view('admin.plan.edit', compact('plan', 'features'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        // if (!Auth::user()->can('plan.edit')) {
        //     abort(403);
        // }

        try {
            DB::beginTransaction();

            // $inputs = $request->except('_token', '_method', 'features');
            $plan->update($request->validated());

            $featuresData = $request->features;
            $existingFeatures = $plan->features;
            $existingFeatureIds = $existingFeatures->pluck('features_type')->toArray();

            return $existingFeatureIds;

            foreach ($featuresData as $featureData) {

                if (isset($featureData['features_type']) && in_array($featureData['features_type'], $existingFeatureIds)) {
                    // Update existing feature
                    $feature = Feature::where('features_type', $featureData['features_type'])->where('plan_id', $plan->id)->first();

                    $feature->update([
                        'name' => $featureData['name'],
                        'value' => $featureData['value'],
                        'features_type' => $featureData['features_type'],
                        'sort_order' => $featureData['sort_order'],
                    ]);
                } else {
                    // Create new feature
                    $feature = new Feature([
                        'name' => $featureData['name'],
                        'value' => $featureData['value'],
                        'features_type' => $featureData['features_type'],
                        'sort_order' => $featureData['sort_order'],
                    ]);

                    $plan->features()->save($feature);
                }
            }

            DB::commit();
            return redirect()->route('plans.index')->with('success', 'Plan updated successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // if (!Auth::user()->can('plan.delete')) {
        //     abort(403);
        // }

        try {
            $plan = Plan::findOrFail($id);
            $plan->delete();
            return redirect()->route('plans.index')->with('success', 'Plan create successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteFeature()
    {
        $feature = Feature::where('id', $_GET['data']['id'])->orWhere('plan_id', $_GET['data']['plan_id'])->first();

        if ($feature) {
            $feature->delete();
            $res = ['success'];
        } else {
            $res = ['error'];
        }
        return response()->json($res);
    }

    public function subscriptions()
    {
        $features = Subscription::with('plan')->latest()->get();

        return view('admin.subscription.subscription-list', compact('features'));
    }
}
