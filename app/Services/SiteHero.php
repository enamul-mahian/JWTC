<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class SiteHero
{
    public function current(): object
    {
        $row=Schema::hasTable('site_hero_settings') ? DB::table('site_hero_settings')->where('id',1)->first() : null;
        if (!$row) return (object)['enabled'=>true,'interval'=>8,'slides'=>[['id'=>'london-evening','path'=>'theme/hero-london.webp','position'=>'center 55%']]];
        $row->slides=array_values(array_filter(json_decode($row->slides,true,32,JSON_THROW_ON_ERROR),fn($slide)=>self::safePath($slide['path']??'')));
        return $row;
    }
    public static function safePath(string $path): bool
    {
        return in_array($path,['theme/hero-london.webp','theme/hero-tower-bridge.webp'],true) || preg_match('/\Auploads\/hero\/[a-f0-9-]{36}\.webp\z/D',$path)===1;
    }
}
