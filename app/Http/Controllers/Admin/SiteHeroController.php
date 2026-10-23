<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\SiteHero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class SiteHeroController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless(in_array(strtolower((string)$request->user()?->user_type),['admin','super_admin'],true),403);
        abort_unless($request->user()->can('application-setting'),403);
    }
    public function index(Request $request, SiteHero $heroes)
    {
        $this->authorizeAdmin($request);
        return response()->view('admin.setting.hero_slides',['hero'=>$heroes->current(),'setting'=>gs()])->header('Cache-Control','private, no-store');
    }
    public function update(Request $request, SiteHero $heroes)
    {
        $this->authorizeAdmin($request);
        $data=$request->validate([
            'enabled'=>'required|boolean','interval'=>'required|integer|between:5,30',
            'slides'=>'sometimes|array|max:5','slides.*.id'=>'required|string|max:36|distinct',
            'slides.*.order'=>'required|integer|between:1,99','slides.*.remove'=>'sometimes|boolean',
            'images'=>'sometimes|array|max:5','images.*'=>'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=5000,max_height=3000',
        ]);
        $current=collect($heroes->current()->slides)->keyBy('id'); $slides=[];
        foreach ($data['slides']??[] as $entry) {
            if (!$current->has($entry['id'])) throw ValidationException::withMessages(['slides'=>'Unknown slide. Reload this page and try again.']);
            if (!empty($entry['remove'])) continue;
            $slides[]=$current[$entry['id']]+['order'=>(int)$entry['order']];
        }
        usort($slides,fn($a,$b)=>$a['order']<=>$b['order']);
        $slides=array_map(function($slide){unset($slide['order']);return $slide;},$slides);
        $files=$request->file('images',[]);
        if (count($slides)+count($files)>5) throw ValidationException::withMessages(['images'=>'Keep up to five slides. Remove an existing slide before adding more.']);
        if (!$slides && !$files) throw ValidationException::withMessages(['slides'=>'Keep at least one background image.']);
        $created=[];
        try {
            foreach($files as $file) {
                $dimensions=getimagesize($file->getRealPath());
                if (!$dimensions || $dimensions[0]*$dimensions[1]>12000000) throw ValidationException::withMessages(['images'=>'Images must be at most 12 megapixels.']);
                $image=imagecreatefromstring(file_get_contents($file->getRealPath()));
                if (!$image) throw ValidationException::withMessages(['images'=>'The image could not be decoded.']);
                $width=min(2560,imagesx($image)); $height=max(1,(int)round(imagesy($image)*$width/imagesx($image)));
                $scaled=imagecreatetruecolor($width,$height);
                imagecopyresampled($scaled,$image,0,0,0,0,$width,$height,imagesx($image),imagesy($image));
                ob_start(); imagewebp($scaled,null,82); $bytes=ob_get_clean();
                imagedestroy($image);imagedestroy($scaled);
                if (!$bytes) throw new \RuntimeException('Image encoding failed');
                $id=(string)Str::uuid();$name=$id.'.webp';
                Storage::disk('hero-public')->put($name,$bytes);$created[]=$name;
                $slides[]=['id'=>$id,'path'=>'uploads/hero/'.$name,'position'=>'center'];
            }
            DB::table('site_hero_settings')->where('id',1)->update(['enabled'=>(bool)$data['enabled'],'interval'=>(int)$data['interval'],'slides'=>json_encode($slides,JSON_THROW_ON_ERROR),'updated_by'=>$request->user()->id,'updated_at'=>now()]);
        } catch (\Throwable $error) {
            foreach($created as $name) Storage::disk('hero-public')->delete($name);
            throw $error;
        }
        return back()->with('success','Homepage slides saved.');
    }
}
