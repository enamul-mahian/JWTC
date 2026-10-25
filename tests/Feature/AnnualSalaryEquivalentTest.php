<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Services\JobPresentation;
use Tests\TestCase;

class AnnualSalaryEquivalentTest extends TestCase
{
    public function test_equivalent_figures_are_derived_from_annual_gross_and_hours(): void
    {
        $job = new Job([
            'minimum_salary'=>26500,
            'maximum_salary'=>28000,
            'salary_type'=>'yearly',
            'additional_salary_info'=>"Payment timing unconfirmed.\nWeekly working hours: 40",
        ]);
        $rates=JobPresentation::salaryEquivalents($job);
        $this->assertNotNull($rates);
        $this->assertSame('£2,208.33 – £2,333.33',$rates['monthly']);
        $this->assertSame('£509.62 – £538.46',$rates['weekly']);
        $this->assertSame('£12.74 – £13.46',$rates['hourly']);
        $this->assertSame(40.0,$rates['hours']);
    }

    public function test_missing_hours_or_unconfirmed_salary_period_cannot_invent_equivalents(): void
    {
        $job=new Job(['minimum_salary'=>26500,'maximum_salary'=>28000,'salary_type'=>'yearly']);
        $this->assertNull(JobPresentation::salaryEquivalents($job));
        $job->additional_salary_info="Weekly working hours: 40";
        $job->salary_type='monthly';
        $this->assertNull(JobPresentation::salaryEquivalents($job));
    }

    public function test_editor_persists_hours_without_database_migration_and_keeps_notes(): void
    {
        $request=\Illuminate\Http\Request::create('/jobs/5','PUT',[
            'weekly_working_hours'=>'37.5',
        ]);
        $data=JobPresentation::fields($request,[
            'additional_salary_info'=>"Pay subject to employer approval.\nWeekly working hours: 40",
        ],new Job);
        $this->assertSame("Pay subject to employer approval.\nWeekly working hours: 37.5",$data['additional_salary_info']);
        $this->assertArrayNotHasKey('weekly_working_hours',$data);
    }

    public function test_editor_and_public_details_have_clear_annual_and_estimated_units(): void
    {
        $form=file_get_contents(resource_path('views/frontend/pages/job/form.blade.php'));
        $details=file_get_contents(resource_path('views/frontend/pages/job/details.blade.php'));
        $this->assertStringContainsString('Minimum annual gross salary',$form);
        $this->assertStringContainsString('value="yearly"',$form);
        $this->assertStringContainsString('Weekly contracted working hours',$form);
        $this->assertStringContainsString("salaryEquivalents(\$jobDetails)",$details);
        $this->assertStringContainsString('Payment frequency: To Be Confirmed',$details);
    }
}
