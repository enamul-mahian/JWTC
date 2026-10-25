<?php
namespace App\Services;
use App\Models\Job;
use App\Models\Dashboard\Company;
class JobEditorData
{
    public function for(Job $job,?Company $company): array
    {
        return ['job'=>$job,'company'=>$company,'setting'=>gs(),
            'jobCategory'=>\App\Models\Dashboard\JobCategory::orderBy('name')->get(['id','name']),
            'degreeLevel'=>\App\Models\Dashboard\DegreeLavel::query()
                ->where(function ($q) use ($job) {
                    $q->whereNull('status')->orWhere('status',1);
                    if ($job->degree_level_id) $q->orWhere('id',$job->degree_level_id);
                })->orderBy('name')->get(['id','name']),
            'degreeNames'=>\App\Models\Dashboard\DegreeName::query()
                ->where(function ($q) use ($job) {
                    $q->where(function ($active) {
                        $active->where(fn ($status) => $status->whereNull('status')->orWhere('status',1))
                            ->whereHas('degreeLavel', fn ($level) => $level->where(
                                fn ($status) => $status->whereNull('status')->orWhere('status',1)
                            ));
                    });
                    if ($job->degree_name_id) $q->orWhere('id',$job->degree_name_id);
                })->orderBy('name')->get(['id','name','degree_lavel_id']),
            'locations'=>\App\Models\Country::whereIn('id',\Illuminate\Support\Facades\DB::table('job_location_options')->where('enabled',true)->select('country_id'))->orWhere('id',$job->country_id)->orderBy('name')->get(['id','name']),
            'jobTypes'=>\App\Models\JobType::orderBy('name')->get(['id','name']),
            'availableSkills'=>\App\Models\Skill::orderBy('skill_name')->get(['id','skill_name']),
        ];
    }
    public static function list(mixed $value): array
    {
        if(is_array($value))return $value;
        $value=json_decode((string)$value,true);return is_array($value)?$value:[];
    }
}
