<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Services\ApplicationChat;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationChatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('application_chat.enabled', true);

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique();
            $t->string('user_type'); $t->string('password')->nullable();
            $t->softDeletes(); $t->timestamps();
        });
        Schema::create('companies', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id');
            $t->string('company_name')->nullable(); $t->timestamps();
        });
        Schema::create('jobs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('company_id');
            $t->string('title'); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('applications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('job_id');
            $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('employer_id');
            $t->integer('status'); $t->string('date_applied');
            $t->decimal('expected_salary', 8, 2); $t->timestamps();
        });

        (require database_path('migrations/2023_12_21_130613_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_10_22_000012_create_private_application_chats.php'))->up();

        DB::table('users')->insert([
            ['id'=>1,'name'=>'Applicant A','email'=>'app1@example.invalid','user_type'=>'job_seeker'],
            ['id'=>2,'name'=>'Applicant B','email'=>'app2@example.invalid','user_type'=>'job_seeker'],
            ['id'=>3,'name'=>'Owner Employer','email'=>'owner@example.invalid','user_type'=>'employer'],
            ['id'=>4,'name'=>'Other Employer','email'=>'otheremp@example.invalid','user_type'=>'employer'],
            ['id'=>5,'name'=>'Regular Admin','email'=>'admin@example.invalid','user_type'=>'admin'],
            ['id'=>6,'name'=>'Support Admin','email'=>'support@example.invalid','user_type'=>'super_admin'],
        ]);
        DB::table('companies')->insert([
            ['id'=>10,'user_id'=>3,'company_name'=>'Company A'],
            ['id'=>20,'user_id'=>4,'company_name'=>'Company B'],
        ]);
        DB::table('jobs')->insert([
            ['id'=>50,'user_id'=>3,'company_id'=>10,'title'=>'Owned Role'],
            ['id'=>60,'user_id'=>4,'company_id'=>20,'title'=>'Other Role'],
        ]);
        DB::table('applications')->insert([
            ['id'=>91,'job_id'=>50,'user_id'=>1,'employer_id'=>10,
                'status'=>1,'date_applied'=>now()->toDateString(),'expected_salary'=>1000],
            ['id'=>92,'job_id'=>60,'user_id'=>2,'employer_id'=>20,
                'status'=>1,'date_applied'=>now()->toDateString(),'expected_salary'=>1200],
        ]);

        Permission::findOrCreate('post-job', 'web');
        Permission::findOrCreate('manage-application-chat', 'web');
        User::findOrFail(3)->givePermissionTo('post-job');
        User::findOrFail(4)->givePermissionTo('post-job');
        User::findOrFail(6)->givePermissionTo('manage-application-chat');
    }

    private function start(): object
    {
        return app(ApplicationChat::class)->create(
            User::findOrFail(1), Application::findOrFail(91)
        );
    }

    public function test_applicant_can_start_once_and_store_encrypted_private_message(): void
    {
        $actor = User::findOrFail(1);
        $first = $this->start();
        $again = $this->start();
        $this->assertSame($first->id, $again->id);

        $msg = app(ApplicationChat::class)->send(
            $actor, $first->id, 'Please review my application.'
        );
        $this->assertSame('Please review my application.', $msg['body']);
        $this->assertDatabaseCount('application_chat_threads', 1);
        $this->assertDatabaseCount('application_chat_events', 1);
        $this->assertDatabaseCount('application_chat_messages', 1);

        $cipher = DB::table('application_chat_messages')->value('body_encrypted');
        $this->assertStringNotContainsString('Please review', $cipher);
        $this->assertSame('Please review my application.', Crypt::decryptString($cipher));
    }

    public function test_only_actual_company_and_job_owner_can_open_an_application_chat(): void
    {
        $chat = app(ApplicationChat::class);
        $application = Application::findOrFail(91);
        $this->assertTrue($chat->allowed(User::findOrFail(1), $application));
        $this->assertTrue($chat->allowed(User::findOrFail(3), $application));
        $this->assertFalse($chat->allowed(User::findOrFail(2), $application));
        $this->assertFalse($chat->allowed(User::findOrFail(4), $application));

        $this->actingAs(User::findOrFail(4))
            ->post(route('application-chat.open',91))
            ->assertForbidden();
        $this->assertDatabaseCount('application_chat_threads', 0);
    }

    public function test_other_applicant_and_employer_cannot_access_thread_or_message_history(): void
    {
        $thread = $this->start();
        app(ApplicationChat::class)->send(User::findOrFail(1), $thread->id, 'My private reference.');

        $this->actingAs(User::findOrFail(2))
            ->get(route('application-chat.thread',$thread->id))->assertNotFound();
        $this->get(route('application-chat.poll',$thread->id))->assertNotFound();

        $this->actingAs(User::findOrFail(4))
            ->postJson('/api/application-messages/threads/'.$thread->id.'/messages', [
                'body'=>'Unauthorised contact'
            ])->assertNotFound();

        $this->assertDatabaseCount('application_chat_messages', 1);
    }

    public function test_admin_requires_a_dedicated_permission(): void
    {
        $thread = $this->start();
        $this->actingAs(User::findOrFail(5))
            ->get(route('application-chat.index'))->assertForbidden();
        $this->get(route('application-chat.thread',$thread->id))->assertForbidden();

        $this->actingAs(User::findOrFail(6))
            ->get(route('application-chat.thread',$thread->id))
            ->assertOk()->assertSee('Private conversation');
    }

    public function test_sender_and_recipient_unread_states_remain_isolated(): void
    {
        $thread = $this->start();
        $service = app(ApplicationChat::class);
        $a = User::findOrFail(1);
        $e = User::findOrFail(3);

        $service->send($a, $thread->id, 'First message');
        $service->send($a, $thread->id, 'Second message');

        $this->assertSame(0, $service->unread($a, $thread));
        $this->assertSame(2, $service->unread($e, $thread));
        $last = $service->markRead($e, $thread->id);
        $this->assertGreaterThan(0, $last);
        $this->assertSame(0, $service->unread($e, $thread));

        $service->send($a, $thread->id, 'Third message');
        $this->assertSame(1, $service->unread($e, $thread));
        $service->markRead($e, $thread->id);
        $this->assertSame(0, $service->unread($e, $thread));
    }

    public function test_closed_chat_blocks_new_messages_until_owner_reopens_it(): void
    {
        $thread = $this->start();
        $chat = app(ApplicationChat::class);
        $owner = User::findOrFail(3);
        $applicant = User::findOrFail(1);

        $closed = $chat->changeStatus($owner, $thread->id, 'closed');
        $this->assertSame('closed', $closed->status);
        try {
            $chat->send($applicant, $thread->id, 'Blocked text');
            $this->fail('Closed conversation allowed a message.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('body', $exception->errors());
        }

        $chat->changeStatus($owner, $thread->id, 'closed');
        $this->assertDatabaseCount('application_chat_events', 2);

        $open = $chat->changeStatus($owner, $thread->id, 'open');
        $this->assertSame('open', $open->status);
        $chat->send($applicant, $thread->id, 'Now allowed');
        $this->assertDatabaseCount('application_chat_messages', 1);
        $this->assertDatabaseCount('application_chat_events', 3);
    }

    public function test_applicant_cannot_close_employer_chat(): void
    {
        $thread = $this->start();
        $this->actingAs(User::findOrFail(1))
            ->post(route('application-chat.status',$thread->id),[
                'status'=>'closed',
            ])->assertForbidden();
    }

    public function test_poll_only_returns_new_messages_and_does_not_leak_ciphertext(): void
    {
        $thread = $this->start();
        $service = app(ApplicationChat::class);
        $one = $service->send(User::findOrFail(1), $thread->id, 'First');
        $service->send(User::findOrFail(3), $thread->id, 'Second');

        $this->actingAs(User::findOrFail(1))
            ->getJson(route('application-chat.poll',$thread->id).'?after='.$one['id'])
            ->assertOk()->assertJsonCount(1,'messages')
            ->assertJsonPath('messages.0.body','Second')
            ->assertDontSee('body_encrypted');
    }

    public function test_html_in_messages_is_escaped_not_rendered_as_javascript(): void
    {
        $thread = $this->start();
        app(ApplicationChat::class)->send(User::findOrFail(1),
            $thread->id, '<script>alert(1)</script>');

        $this->actingAs(User::findOrFail(1))
            ->get(route('application-chat.thread',$thread->id))
            ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertHeader('X-Frame-Options','DENY')
            ->assertHeader('Cache-Control','max-age=0, no-store, private');
    }

    public function test_feature_flag_off_rejects_private_messaging(): void
    {
        $this->actingAs(User::findOrFail(1));
        config()->set('application_chat.enabled', false);
        $this->get(route('application-chat.index'))->assertNotFound();
    }

    public function test_guest_and_unprivileged_api_clients_are_rejected(): void
    {
        $this->get(route('application-chat.index'))->assertRedirect();
        $this->getJson('/api/application-messages')->assertUnauthorized();

        Sanctum::actingAs(User::findOrFail(2), ['*']);
        $this->getJson('/api/application-messages')->assertOk();
        $this->getJson('/api/application-messages/threads/9999')->assertNotFound();
    }
    public function test_large_conversation_history_is_cursor_paginated_and_private(): void
    {
        $thread = $this->start();
        $user = User::findOrFail(1);
        $chat = app(ApplicationChat::class);
        for ($i = 1; $i <= 43; $i++) {
            $chat->send($user, $thread->id, 'Message number ' . $i);
        }

        $latest = $chat->messages($thread);
        $this->assertCount(40, $latest);
        $this->assertSame('Message number 4', $latest->first()['body']);
        $this->assertSame('Message number 43', $latest->last()['body']);

        $this->actingAs($user)
            ->getJson(route('application-chat.history',$thread->id) .
                '?before=' . $latest->first()['id'])
            ->assertOk()
            ->assertJsonCount(3, 'messages')
            ->assertJsonPath('messages.0.body', 'Message number 1')
            ->assertJsonPath('has_more', false);

        $this->actingAs(User::findOrFail(2))
            ->getJson(route('application-chat.history',$thread->id) .
                '?before=' . $latest->first()['id'])
            ->assertNotFound();
    }

}
