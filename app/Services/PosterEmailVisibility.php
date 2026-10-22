<?php
namespace App\Services;
use App\Models\ApplicationSetting;
class PosterEmailVisibility
{
    private ?bool $visible = null;
    public function enabled(): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('application_settings', 'show_poster_email')) return false;
        return $this->visible ??= (bool) ApplicationSetting::latest('id')->value('show_poster_email');
    }
}
