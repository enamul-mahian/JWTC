<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Country;
use App\Models\IndustryType;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Skill;
use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\DegreeName;
use App\Models\Dashboard\JobCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getHomePageData(): JsonResponse
    {
        return response()->json([
            'jobs' => Job::with(['company', 'country', 'job_category'])
                ->where('is_publish', 1)
                ->where(function ($q) {
                    $q->whereNull('application_deadline')
                      ->orWhereDate('application_deadline', '>=', today());
                })
                ->latest()
                ->limit(12)
                ->get(),
            'job_categories' => JobCategory::orderBy('name')->get(['id', 'name']),
            'countries' => Country::where('status', 1)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function jobDetails(int $id): JsonResponse
    {
        return response()->json([
            'job' => Job::with(['company', 'country', 'job_category', 'degree_level', 'degree_name'])
                ->where('is_publish', 1)->findOrFail($id),
        ]);
    }

    public function getAllLocations(): JsonResponse
    {
        return response()->json(Country::where('status', 1)->orderBy('name')->get(['id', 'name']));
    }

    public function getJobCategories(): JsonResponse
    {
        return response()->json(JobCategory::orderBy('name')->get(['id', 'name']));
    }

    public function getDegreeLevel(): JsonResponse
    {
        return response()->json(DegreeLavel::orderBy('name')->get(['id', 'name']));
    }

    public function getDegreeName(Request $request): JsonResponse
    {
        $data = $request->validate([
            'degree_level_id' => ['nullable', 'integer', 'exists:degree_lavels,id'],
        ]);
        $query = DegreeName::query();
        if (isset($data['degree_level_id'])) {
            $query->where('degree_lavel_id', $data['degree_level_id']);
        }
        return response()->json($query->orderBy('name')->get(['id', 'name']));
    }

    public function getJobType(): JsonResponse
    {
        return response()->json(JobType::orderBy('name')->get(['id', 'name']));
    }

    public function getIndustryType(): JsonResponse
    {
        return response()->json(IndustryType::orderBy('name')->get(['id', 'name']));
    }

    public function categoryWiseSkills(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_category_id' => ['required', 'integer', 'exists:job_categories,id'],
        ]);
        return response()->json(Skill::where('job_category_id', $data['job_category_id'])
            ->orderBy('skill_name')->get(['id', 'skill_name']));
    }

    public function contactStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:255'],
        ]);

        $contact = Contact::create($data);
        return response()->json(['message' => 'Message received.', 'id' => $contact->id], 201);
    }
}
