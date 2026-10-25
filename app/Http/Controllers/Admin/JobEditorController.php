<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Requests\StoreJobRequest;
use App\Models\Dashboard\Company;
use App\Enum\JobStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Job;
use App\Services\JobEditorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class JobEditorController extends Controller
{
    private function reviewer(Request $request): void
    {
        abort_unless(in_array(strtolower((string)$request->user()?->user_type),['admin','super_admin'],true) && $request->user()->can('active-job'),403);
    }
    public function index(Request $request)
    {
        $this->reviewer($request);$jobs=Job::with(['company','job_category'])->latest()->paginate(25);
        return response()->view('admin.job.manage',['jobs'=>$jobs,'setting'=>gs()])->header('Cache-Control','private, no-store');
    }
    public function create(Request $request, JobEditorData $editor)
    {
        $this->reviewer($request);
        $companies = Company::query()
            ->whereNotNull('user_id')
            ->whereHas('user', fn ($q) => $q->whereNotNull('employer_approved_at'))
            ->orderBy('company_name')->get(['id', 'company_name', 'user_id']);
        return response()->view('admin.job.create', $editor->for(new Job(), null)
            + ['adminCreate' => true, 'companies' => $companies])
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(StoreJobRequest $request)
    {
        $this->reviewer($request);
        $extra = $request->validate([
            'company_id' => ['required','integer', Rule::exists('companies','id')],
            'is_publish' => ['required','boolean'],
            'posting_permission' => ['accepted'],
            'application_deadline' => ['required','date','after_or_equal:today'],
        ]);
        // Admin may post for an already approved employer, never impersonate an
        // unapproved or missing company. Only the server sets job ownership.
        $company = Company::query()->whereKey($extra['company_id'])
            ->whereHas('user', fn ($q) => $q->whereNotNull('employer_approved_at'))->first();
        if (!$company) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'company_id' => 'Choose an approved employer account for this job.',
            ]);
        }
        $data = \App\Services\JobPresentation::fields($request, $request->safe()->except(
            ['company_id', 'is_publish', 'posting_permission']
        ), null);
        foreach (['employment_status','job_level','work_place','other_benefits','skills','gender'] as $name) {
            $data[$name] = json_encode($data[$name] ?? [], JSON_THROW_ON_ERROR);
        }
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(12));
        $data['company_id'] = $company->id;
        $data['user_id'] = $company->user_id;
        $data['is_publish'] = (int) $extra['is_publish'];
        $data['job_status'] = $extra['is_publish'] ? JobStatus::APPROVED : JobStatus::PENDING;
        $job = DB::transaction(function () use ($data, $request) {
            $job = Job::create($data);
            DB::table('job_edit_events')->insert([
                'job_id' => $job->id, 'actor_id' => $request->user()->id,
                'changed_fields' => json_encode(['admin_created', 'company_id', 'is_publish'], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
            return $job;
        });
        return redirect()->route('admin.jobs.edit', $job)
            ->with('success', $extra['is_publish']
                ? 'Job created and published under the selected employer.'
                : 'Job created as an unpublished draft.');
    }

    public function edit(Request $request,Job $job,JobEditorData $editor)
    {
        $this->reviewer($request);
        return response()->view('admin.job.edit',$editor->for($job,$job->company)+['adminEdit'=>true])->header('Cache-Control','private, no-store');
    }
    public function update(UpdateJobRequest $request,Job $job)
    {
        $this->reviewer($request);$data=\App\Services\JobPresentation::fields($request,$request->validated(),$job);
        $extra=$request->validate(['is_publish'=>'required|boolean','created_at'=>'nullable|date']);
        foreach(['employment_status','job_level','work_place','other_benefits','skills','gender'] as $key)$data[$key]=json_encode($data[$key]??[],JSON_THROW_ON_ERROR);
        unset($data['company_id'],$data['user_id'],$data['slug']);
        $data['is_publish']=(int)$extra['is_publish'];
        if(!empty($extra['created_at']) && substr($extra['created_at'],0,10)!==$job->created_at?->format('Y-m-d'))$data['created_at']=$extra['created_at'];
        DB::transaction(function()use($job,$data,$request){
            $record=Job::lockForUpdate()->findOrFail($job->id);$record->fill($data);$fields=array_keys($record->getDirty());$record->save();
            if($fields)DB::table('job_edit_events')->insert(['job_id'=>$record->id,'actor_id'=>$request->user()->id,'changed_fields'=>json_encode($fields,JSON_THROW_ON_ERROR),'created_at'=>now()]);
        });
        return redirect()->route('admin.jobs.edit',$job)->with('success','Job updated. Ownership and original URL are preserved.');
    }
}
