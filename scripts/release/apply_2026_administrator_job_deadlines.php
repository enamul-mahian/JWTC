<?php

// Controlled one-time closing-date revision. NEVER backdate created_at.
// Job IDs, employer attribution, applications and original history are retained.
// Requested 14–16 Oct dates are reference-only and explicitly NOT original posting dates.
ini_set('display_errors', '0');
ini_set('log_errors', '0');

try {
    $home = realpath(getenv('HOME') ?: '');
    $root = realpath('.');
    if (!$home || !$root || $root !== $home.'/joingallagher.uk'
        || !is_file($root.'/vendor/autoload.php')) throw new RuntimeException('Invalid production root');

    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $ids = [11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,28,29,30,31,32,33,34,35,36,37,38];
    $titles = [
       11=>'Restaurant General Manager', 12=>'Assistant Restaurant General Manager',
       13=>'KFC Shift Supervisor', 14=>'KFC Cashier', 15=>'Food Server (KFC Hero)',
       16=>'KFC Dishwasher', 17=>'Customer Assistant', 18=>'Pharmacy Manager Camden',
       19=>'Florist',20=>'Tesco Shift Leader',21=>'Team Member - The Cafe',
       22=>'Tesco Colleague',23=>'Tesco Cashier',24=>'Delivery Drivers',
       25=>'Delivery Driver – KFC UK',26=>'Driver Job Vacancy – Morrisons UK',
       28=>'Farm Worker',29=>'Basic Worker - Packaging Factory',30=>'Section Leader',
       31=>'Security Colleague',32=>'Warehouse Department Manager',
       33=>'Customer Service Representative',34=>'Supervisor',
       35=>'Sales Floor Associate',36=>'SOC 2131 - IT Manager',
       37=>'Crew Member',38=>'SOC 4215 — Personal Assistants and Other Secretaries',
    ];
    if (count($ids) !== 27 || count($titles) !== 27) throw new RuntimeException('Invalid roster');
    $db = \Illuminate\Support\Facades\DB::connection();
    $db->beginTransaction();
    try {
        $rows = $db->table('jobs')->whereNull('deleted_at')->where('is_publish', 1)
            ->orderBy('id')->lockForUpdate()->get([
                'id','title','application_deadline','created_at','updated_at',
                'company_id','user_id','is_publish','job_status',
            ]);
        if ($rows->count() !== 27 || $rows->pluck('id')->map(fn ($id) => (int)$id)->all() !== $ids) {
            throw new RuntimeException('Published job roster changed. No dates updated.');
        }
        $snapshot = [];
        foreach ($rows as $record) {
            $id = (int)$record->id;
            if ($record->title !== $titles[$id]) throw new RuntimeException('Job title changed for '.$id);
            $deadline = \App\Services\JobDateRevision::deadlineForId($id);
            $reference = \App\Services\JobDateRevision::referenceForId($id);
            if (!$deadline || !$reference || $deadline < '2026-11-27' || $deadline > '2026-12-02') {
                throw new RuntimeException('Invalid approved deadline mapping for '.$id);
            }
            $snapshot[] = [
                'id'=>$id,'title'=>$record->title,'company_id'=>$record->company_id,
                'user_id'=>$record->user_id,'is_publish'=>$record->is_publish,
                'job_status'=>$record->job_status,
                'original_application_deadline'=>$record->application_deadline,
                'original_created_at'=>$record->created_at,
                'original_updated_at'=>$record->updated_at,
                'administrator_reference_date_not_original_posted_on'=>$reference,
                'new_administrator_supplied_deadline_pending_employer_confirmation'=>$deadline,
            ];
        }

        $dir = $home.'/jwtc-private-job-date-revisions';
        if (is_link($dir) || (!is_dir($dir) && !mkdir($dir,0700))) {
            throw new RuntimeException('Private audit directory unavailable');
        }
        $manifest = $dir.'/20261026-original-dates-and-admin-deadlines.json';
        if (file_exists($manifest) || is_link($manifest)) {
            throw new RuntimeException('This one-time revision already attempted; review manifest before repeating');
        }
        $raw = json_encode([
            'kind'=>'administrator-supplied-deadline-not-employer-verified',
            'original_created_at_is_preserved'=>true,
            'collected_utc'=>gmdate('c'),
            'jobs'=>$snapshot,
        ],JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        $fh = fopen($manifest,'x');
        if (!$fh || fwrite($fh,$raw)!==strlen($raw) || !fflush($fh)) {
            throw new RuntimeException('Cannot safely record previous dates');
        }
        fclose($fh);
        chmod($manifest,0600);

        foreach ($rows as $record) {
            $id=(int)$record->id;
            $deadline=\App\Services\JobDateRevision::deadlineForId($id);
            $updated=$db->table('jobs')->where('id',$id)->whereNull('deleted_at')
                ->where('is_publish',1)->where('title',$record->title)
                ->where('company_id',$record->company_id)->where('user_id',$record->user_id)
                ->update(['application_deadline'=>$deadline,'updated_at'=>now()]);
            if($updated !== 1) throw new RuntimeException('Unexpected update result for job '.$id);
        }
        foreach ($ids as $id) {
            $actual=$db->table('jobs')->where('id',$id)->first(['application_deadline','created_at','company_id','user_id']);
            $prior=$snapshot[array_search($id,$ids,true)];
            if(!$actual
                || substr((string)$actual->application_deadline,0,10)!==$prior['new_administrator_supplied_deadline_pending_employer_confirmation']
                || (string)$actual->created_at!==(string)$prior['original_created_at']
                || (int)$actual->company_id!==(int)$prior['company_id']
                || (int)$actual->user_id!==(int)$prior['user_id']) {
                throw new RuntimeException('Verification failed before commit for job '.$id);
            }
        }
        $db->commit();
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }

    echo "PASS: 27 website-administrator-supplied closing dates now fall 27 Nov–2 Dec 2026\n";
    echo "PASS: 27 original created_at timestamps, job owners, applicants and job IDs preserved\n";
    echo "PASS: original deadlines retained privately; reference dates are never claimed as actual postings\n";
    echo "INFO: source employer approval for renewed recruitment has not been independently verified\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "DATE_REVISION_STOP ".get_class($e)." ".preg_replace('/[^a-zA-Z0-9 _.-]/','',(string)$e->getMessage())."\n");
    exit(1);
}
