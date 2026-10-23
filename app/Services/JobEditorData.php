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
            'degreeLevel'=>\App\Models\Dashboard\DegreeLavel::orderBy('name')->get(['id','name']),
            'degreeNames'=>\App\Models\Dashboard\DegreeName::orderBy('name')->get(['id','name']),
            'locations'=>\App\Models\Country::orderBy('name')->get(['id','name']),
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
