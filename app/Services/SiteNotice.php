<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class SiteNotice
{
    public function current(): ?object
    {
        if (!Schema::hasTable('site_notice_settings')) return null;
        $row=DB::table('site_notice_settings')->where('id',1)->first();
        if (!$row) return null;
        $row->items=json_decode($row->items,true,32,JSON_THROW_ON_ERROR);
        return $row;
    }
    public static function safeUrl(?string $url): bool
    {
        if ($url===null || $url==='') return true;
        if (preg_match('/[\x00-\x20\\\\]/', $url)) return false;
        if (str_starts_with($url,'/')) return !str_starts_with($url,'//');
        return strtolower((string)parse_url($url,PHP_URL_SCHEME))==='https' && filter_var($url,FILTER_VALIDATE_URL)!==false;
    }
}
