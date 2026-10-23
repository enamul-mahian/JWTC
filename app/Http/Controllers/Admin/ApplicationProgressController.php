<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ApplicationStatusCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ApplicationProgressController extends Controller
{
    private function reviewer(Request $request): void
    {
        abort_unless(in_array(strtolower((string)$request->user()?->user_type),['admin','super_admin'],true) && $request->user()->can('active-job'),403);
    }
    public function index(Request $request,ApplicationStatusCatalog $catalog)
    {
        $this->reviewer($request);$applications=Application::with('job.company')->latest('id')->paginate(25);
        return response()->view('admin.application_progress.index',['applications'=>$applications,'stages'=>collect($catalog->all())->keyBy('id'),'setting'=>gs()])->header('Cache-Control','private, no-store');
    }
    public function show(Request $request,int $application,ApplicationStatusCatalog $catalog)
    {
        $this->reviewer($request);$record=Application::with(['job.company','job.country'])->findOrFail($application);
        $events=DB::table('application_status_events')->where('application_id',$record->id)->orderBy('created_at')->orderBy('id')->get();
        return response()->view('admin.application_progress.show',['application'=>$record,'events'=>$events,'statuses'=>$catalog->all(),'current'=>$catalog->find((int)$record->status),'setting'=>gs(),'adminView'=>true])->header('Cache-Control','private, no-store');
    }
    public function update(Request $request,int $application)
    {
        $this->reviewer($request);$data=$request->validate(['status'=>'required|integer|min:1','note'=>'nullable|string|max:1000']);
        app(\App\Services\ApplicationStatusManager::class)->transition(Application::findOrFail($application),(int)$data['status'],$request->user(),'admin',$data['note']??null);
        return redirect()->route('admin.application-progress.show',$application)->with('success','Application progress updated and recorded.');
    }
}
