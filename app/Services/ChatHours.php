<?php
namespace App\Services;
use Carbon\CarbonImmutable;
class ChatHours
{
    public function open(object $settings,?CarbonImmutable $instant=null): bool
    {
        if(empty($settings->schedule_enabled))return true;
        $now=($instant??CarbonImmutable::now())->setTimezone($settings->timezone?:'UTC');
        $hours=json_decode($settings->weekly_hours??'[]',true);if(!is_array($hours))return false;
        $day=$now->dayOfWeekIso;$minute=$now->hour*60+$now->minute;
        $clock=static fn($value)=>(int)substr($value,0,2)*60+(int)substr($value,3,2);
        $today=$hours[$day]??null;
        if($today && isset($today['start'],$today['end'])){
            $start=$clock($today['start']);$end=$clock($today['end']);
            if($start<$end && $minute>=$start && $minute<$end)return true;
            if($start>$end && $minute>=$start)return true;
        }
        $previous=$hours[$day===1?7:$day-1]??null;
        return $previous && isset($previous['start'],$previous['end']) && $clock($previous['start'])>$clock($previous['end']) && $minute<$clock($previous['end']);
    }
}
