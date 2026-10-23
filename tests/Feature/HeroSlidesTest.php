<?php
namespace Tests\Feature;
use App\Models\User;
use App\Services\SiteHero;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class HeroSlidesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();$this->artisan('migrate',['--force'=>true])->assertSuccessful();
        DB::table('job_categories')->insert(['id'=>804,'name'=>'Hero fixture','status'=>1]);
        Storage::fake('hero-public');
    }
    private function actor(string $type='admin',bool $permission=true): User
    {
        static $n=0;$n++;
        $user=User::create(['name'=>'Hero fixture','email'=>'hero'.$n.'@example.test','phone'=>'777'.str_pad((string)$n,8,'0',STR_PAD_LEFT),'password'=>'SyntheticPassword123!','user_type'=>$type,'status'=>1,'job_category_id'=>804]);
        if($permission){Permission::findOrCreate('application-setting','web');$user->givePermissionTo('application-setting');}return $user;
    }
    private function data(): array
    {
        return ['enabled'=>true,'interval'=>8,'slides'=>[['id'=>'london-evening','order'=>2],['id'=>'london-detail','order'=>1]]];
    }
    public function test_admin_role_and_settings_permission_are_both_required(): void
    {
        $this->get('/admin/hero-slides')->assertRedirect('/login');
        foreach([['job_seeker',true],['admin',false]] as [$type,$permission]) {
            $this->actingAs($this->actor($type,$permission))->get('/admin/hero-slides')->assertForbidden();
            $this->post('/admin/hero-slides',$this->data())->assertForbidden();
        }
        $this->actingAs($this->actor())->get('/admin/hero-slides')->assertOk()->assertSee('Seconds between slides');
    }
    public function test_upload_is_reencoded_and_admin_can_reorder_and_disable_slideshow(): void
    {
        $admin=$this->actor();$this->actingAs($admin);
        $data=$this->data();$data['enabled']=false;$data['interval']=15;$data['images']=[UploadedFile::fake()->image('background.png',1000,500)];
        $this->post('/admin/hero-slides',$data)->assertRedirect()->assertSessionHasNoErrors();
        $hero=app(SiteHero::class)->current();$this->assertSame('london-detail',$hero->slides[0]['id']);
        $this->assertFalse((bool)$hero->enabled);$this->assertSame(15,(int)$hero->interval);$this->assertCount(3,$hero->slides);
        $path=basename($hero->slides[2]['path']);Storage::disk('hero-public')->assertExists($path);
        $bytes=Storage::disk('hero-public')->get($path);$this->assertSame('RIFF',substr($bytes,0,4));$this->assertSame('WEBP',substr($bytes,8,4));
        $this->assertSame((int)$admin->id,(int)DB::table('site_hero_settings')->value('updated_by'));
    }
    public function test_bad_images_unknown_slides_empty_gallery_and_invalid_interval_are_rejected(): void
    {
        $this->actingAs($this->actor());$before=DB::table('site_hero_settings')->first();
        $data=$this->data();$data['interval']=2;$this->post('/admin/hero-slides',$data)->assertSessionHasErrors('interval');
        $data=$this->data();$data['slides'][0]['id']='../../secret';$this->post('/admin/hero-slides',$data)->assertSessionHasErrors('slides');
        $data=$this->data();$data['images']=[UploadedFile::fake()->create('unsafe.svg',1,'image/svg+xml')];$this->post('/admin/hero-slides',$data)->assertSessionHasErrors('images.0');
        $data=$this->data();$data['slides']=[];$this->post('/admin/hero-slides',$data)->assertSessionHasErrors('slides');
        $this->assertEquals($before,DB::table('site_hero_settings')->first());
        $this->assertFalse(SiteHero::safePath('uploads/hero/../../.env'));
    }
    public function test_public_home_has_slideshow_install_assets_and_discoverable_admin_link(): void
    {
        $this->get('/')->assertOk()->assertSee('data-hero-slides',false)->assertSee('theme/hero-london.webp')->assertSee('site.webmanifest')->assertSee('pwa/install.js')->assertSee('Admin login')->assertDontSee('commons.wikimedia.org')->assertDontSee('Fred Cunha');
        $manifest=json_decode(file_get_contents(public_path('site.webmanifest')),true,32,JSON_THROW_ON_ERROR);
        $this->assertSame('standalone',$manifest['display']);
        foreach($manifest['icons'] as $icon){$this->assertFileExists(public_path($icon['src']));$size=getimagesize(public_path($icon['src']));$this->assertSame($icon['sizes'],$size[0].'x'.$size[1]);}
    }
}
