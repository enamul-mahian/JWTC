<?php
// One-time, backup-guarded 2026 indicative market salary refresh.
// Updates advertisement pay fields only; never changes employer ownership,
// location, publication, posting dates, deadlines or applications.
ini_set('display_errors', '0');
ini_set('log_errors', '0');

try {
    $home = realpath(getenv('HOME') ?: '');
    $root = realpath('.');
    if (!$home || !$root || $root !== $home.'/joingallagher.uk'
        || !is_file($root.'/vendor/autoload.php')) throw new RuntimeException('Invalid production root');
    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    // No jurisdiction is silently changed. The historic Antigua and Barbuda
    // record receives a clearly disclaimed UK occupational COMPARATOR only.
    // These are indicative 2026 estimates, not advertised employer contracts.
    $plan = [
      11 => ['Restaurant General Manager',107,38000,55000,'yearly','EUR','ireland-restaurant-general-manager'],
      12 => ['Assistant Restaurant General Manager',107,32000,43000,'yearly','EUR','ireland-assistant-manager'],
      13 => ['KFC Shift Supervisor',107,16,19,'hourly','EUR','irish-floor-and-supervisor'],
      14 => ['KFC Cashier',235,13,16,'hourly','GBP','uk-living-wage'],
      15 => ['Food Server (KFC Hero)',10,13,16,'hourly','GBP','uk-comparison-only'],
      16 => ['KFC Dishwasher',107,15,17,'hourly','EUR','irish-living-wage'],
      17 => ['Customer Assistant',235,13,16,'hourly','GBP','uk-retail-assistant'],
      18 => ['Pharmacy Manager Camden',235,40000,65000,'yearly','GBP','uk-pharmacist'],
      19 => ['Florist',235,25000,28000,'yearly','GBP','uk-florist'],
      20 => ['Tesco Shift Leader',107,16,19,'hourly','EUR','irish-floor-and-supervisor'],
      21 => ['Team Member - The Cafe',107,15,17,'hourly','EUR','irish-living-wage'],
      22 => ['Tesco Colleague',250,14,16,'hourly','GBP','uk-retail-assistant'],
      23 => ['Tesco Cashier',235,13,16,'hourly','GBP','uk-retail-assistant'],
      24 => ['Delivery Drivers',107,16,20,'hourly','EUR','irish-living-wage'],
      25 => ['Delivery Driver – KFC UK',107,15,19,'hourly','EUR','irish-living-wage'],
      26 => ['Driver Job Vacancy – Morrisons UK',235,13,16,'hourly','GBP','uk-delivery-driver'],
      28 => ['Farm Worker',235,13,17,'hourly','GBP','uk-farm-worker'],
      29 => ['Basic Worker - Packaging Factory',235,13,16,'hourly','GBP','uk-warehouse-operative'],
      30 => ['Section Leader',235,14,18,'hourly','GBP','uk-supervisor'],
      31 => ['Security Colleague',235,13,17,'hourly','GBP','uk-living-wage'],
      32 => ['Warehouse Department Manager',235,30000,42000,'yearly','GBP','uk-warehouse-manager'],
      33 => ['Customer Service Representative',235,13,16,'hourly','GBP','uk-living-wage'],
      34 => ['Supervisor',107,16,19,'hourly','EUR','irish-floor-and-supervisor'],
      35 => ['Sales Floor Associate',235,13,16,'hourly','GBP','uk-retail-assistant'],
      36 => ['SOC 2131 - IT Manager',250,35000,60000,'yearly','GBP','uk-it-manager'],
      37 => ['Crew Member',235,13,16,'hourly','GBP','uk-living-wage'],
      38 => ['SOC 4215 — Personal Assistants and Other Secretaries',250,28000,40000,'yearly','GBP','uk-personal-assistant'],
    ];
    $db = Illuminate\Support\Facades\DB::connection();
    $db->beginTransaction();
    try {
      $rows = $db->table('jobs')->whereNull('deleted_at')->whereIn('id',array_keys($plan))->lockForUpdate()
        ->get(['id','title','country_id','minimum_salary','maximum_salary','salary_type','additional_salary_info','company_id','user_id','application_deadline','created_at']);
      if (count($rows) !== count($plan)) throw new RuntimeException('Job roster changed since audit');
      $before = [];
      foreach ($rows as $job) {
        [$title,$country,$min,$max,$period,$currency,$reference] = $plan[$job->id];
        if ($job->title !== $title || (int)$job->country_id !== $country) {
          throw new RuntimeException('Job ID/title/country mismatch for '.$job->id);
        }
        if (str_contains((string)$job->additional_salary_info,'2026 indicative salary benchmark')) {
          throw new RuntimeException('Existing benchmark detected; refusing to overwrite: '.$job->id);
        }
        if ($min > $max || $min < 13 || !in_array($period,['hourly','yearly'],true)) {
          throw new RuntimeException('Invalid proposed wage for '.$job->id);
        }
        $before[] = [
          'id'=>$job->id, 'title'=>$job->title, 'country_id'=>$job->country_id,
          'minimum_salary'=>$job->minimum_salary,'maximum_salary'=>$job->maximum_salary,
          'salary_type'=>$job->salary_type,'additional_salary_info'=>$job->additional_salary_info,
          'company_id'=>$job->company_id,'user_id'=>$job->user_id,
          'application_deadline'=>$job->application_deadline,'created_at'=>$job->created_at,
          'proposed'=>compact('min','max','period','currency','reference'),
        ];
      }

      // Must leave a private, immutable-before-update restoration manifest.
      $dir=$home.'/jwtc-private-salary-revisions';
      if (is_link($dir) || (!is_dir($dir) && !mkdir($dir,0700))) throw new RuntimeException('Private review root unavailable');
      $path=$dir.'/20261025-wage-benchmark-originals.json';
      if (file_exists($path) || is_link($path)) throw new RuntimeException('Salary revision already attempted');
      $json=json_encode(['date_utc'=>gmdate('c'),'kind'=>'benchmark-not-contract','rows'=>$before], JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
      $handle=fopen($path,'x');
      if (!$handle || fwrite($handle,$json)!==strlen($json)) throw new RuntimeException('Cannot retain salary originals');
      fclose($handle);
      chmod($path,0600);

      $marker='2026 indicative salary benchmark';
      $count=0;
      foreach ($rows as $job) {
        [$title,$country,$min,$max,$period,$currency,$reference] = $plan[$job->id];
        $note = '<p><strong>2026 indicative salary benchmark (not employer-confirmed pay):</strong> '.
          'Role-based comparison from public UK or Irish employment data; '.$currency.' '.
          'gross per '.$period.'. Confirm the actual offer, currency, worksite, hours and contract directly with the employer. '.
          'The advertised job may have expired and this benchmark does not reopen recruitment. '.
          'Reference: '.htmlspecialchars($reference, ENT_QUOTES,'UTF-8').'.</p>';
        if ($job->id===15) $note.='<p><strong>Location warning:</strong> This job currently records Antigua and Barbuda. '.
          'The UK pay comparison is NOT a local wage rate and does not establish the job location.</p>';
        // The original additional-salary/benefits prose is retained verbatim
        // in the secure rollback manifest; it may contain obsolete wage claims.
        $changed = $db->table('jobs')->where('id',$job->id)->whereNull('deleted_at')
          ->where('title',$title)->where('country_id',$country)->where('salary_type',$job->salary_type)
          ->update([
            'minimum_salary'=>$min,'maximum_salary'=>$max,'salary_type'=>$period,
            'additional_salary_info'=>$note,'updated_at'=>now()
          ]);
        if ($changed!==1) throw new RuntimeException('Unexpected row update count '.$job->id);
        $count++;
      }
      if ($count!==27) throw new RuntimeException('Expected 27 reviewed advertisements');
      $db->commit();
    } catch(Throwable $error) {
      $db->rollBack();
      throw $error;
    }
    echo 'PASS: 27 salary benchmarks updated with market-only disclosure and no employer offer claims'."\n";
    echo 'PASS: exact original salary rows saved privately for restoration'."\n";
    echo 'INFO: no posting dates, deadlines, locations, vacancies, companies or applicant records changed'."\n";
} catch (Throwable $error) {
    fwrite(STDERR,'SALARY_REVISION_STOP '.get_class($error).' '.preg_replace('/[^a-zA-Z0-9 _-]/','', $error->getMessage())."\n");
    exit(1);
}
