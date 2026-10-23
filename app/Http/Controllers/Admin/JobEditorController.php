<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateJobRequest;
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
    public function edit(Request $request,Job $job,JobEditorData $editor)
    {
        $this->reviewer($request);
        return response()->view('admin.job.edit',$editor->for($job,$job->company)+['adminEdit'=>true])->header('Cache-Control','private, no-store');
    }
    public function update(UpdateJobRequest $request,Job $job)
    {
        $this->reviewer($request);$data=$request->validated();
        $extra=$request->validate(['is_publish'=>'required|boolean','created_at'=>'nullable|date']);
        foreach(['employment_status','job_level','work_place','other_benefits','skills','gender'] as $key)$data[$key]=json_encode($data[$key]??[],JSON_THROW_ON_ERROR);
        unset($data['company_id'],$data['user_id'],$data['slug']);
        $data['is_publish']=(int)$extra['is_publish'];
        if(!empty($extra['created_at']))$data['created_at']=$extra['created_at'];
        DB::transaction(function()use($job,$data,$request){
            $record=Job::lockForUpdate()->findOrFail($job->id);$record->fill($data);$fields=array_keys($record->getDirty());$record->save();
            if($fields)DB::table('job_edit_events')->insert(['job_id'=>$record->id,'actor_id'=>$request->user()->id,'changed_fields'=>json_encode($fields,JSON_THROW_ON_ERROR),'created_at'=>now()]);
        });
        return redirect()->route('admin.jobs.edit',$job)->with('success','Job updated. Ownership and original URL are preserved.');
    }
}
