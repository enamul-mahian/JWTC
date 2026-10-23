<?php
namespace Tests\Feature;
use App\Services\ChatHours;
use Carbon\CarbonImmutable;
use Tests\TestCase;
class ChatHoursTest extends TestCase
{
    public function test_weekly_hours_timezone_boundaries_overnight_and_week_wrap(): void
    {
        $hours=app(ChatHours::class);$settings=(object)['schedule_enabled'=>true,'timezone'=>'Europe/London','weekly_hours'=>json_encode([1=>['start'=>'09:00','end'=>'17:00'],7=>['start'=>'22:00','end'=>'02:00']])];
        $this->assertTrue($hours->open($settings,CarbonImmutable::parse('2026-10-26 09:00:00','Europe/London')));
        $this->assertFalse($hours->open($settings,CarbonImmutable::parse('2026-10-26 17:00:00','Europe/London')));
        $this->assertTrue($hours->open($settings,CarbonImmutable::parse('2026-10-26 01:00:00','Europe/London')));
        $this->assertFalse($hours->open($settings,CarbonImmutable::parse('2026-10-26 02:00:00','Europe/London')));
        $this->assertFalse($hours->open($settings,CarbonImmutable::parse('2026-10-27 11:00:00','Europe/London')));
        $settings->schedule_enabled=false;$this->assertTrue($hours->open($settings));
    }
}
