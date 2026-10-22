<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SupportCenter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SupportCenterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('support_center.enabled', true);

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique();
            $t->string('user_type'); $t->string('password')->nullable();
            $t->softDeletes(); $t->timestamps();
        });
        (require database_path('migrations/2023_12_21_130613_create_permission_tables.php'))->up();
        (require database_path('migrations/2026_10_22_000014_create_support_ticket_and_refund_tables.php'))->up();

        DB::table('users')->insert([
            ['id'=>1,'name'=>'Applicant','email'=>'applicant@example.invalid','user_type'=>'job_seeker'],
            ['id'=>2,'name'=>'Other Applicant','email'=>'other@example.invalid','user_type'=>'job_seeker'],
            ['id'=>3,'name'=>'Employer','email'=>'employer@example.invalid','user_type'=>'employer'],
            ['id'=>4,'name'=>'Admin No Access','email'=>'unknown-admin@example.invalid','user_type'=>'admin'],
            ['id'=>5,'name'=>'Support Agent','email'=>'support@example.invalid','user_type'=>'admin'],
            ['id'=>6,'name'=>'Refund Reviewer','email'=>'reviewer@example.invalid','user_type'=>'admin'],
            ['id'=>7,'name'=>'Settlement Officer','email'=>'settlement@example.invalid','user_type'=>'super_admin'],
        ]);

        foreach (['manage-support-tickets','review-refund-requests',
            'confirm-refund-settlement'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        User::findOrFail(5)->givePermissionTo('manage-support-tickets');
        User::findOrFail(6)->givePermissionTo('review-refund-requests');
        User::findOrFail(7)->givePermissionTo('confirm-refund-settlement');
    }

    private function data(string $category='complaint'): array
    {
        return [
            'category'=>$category,
            'subject'=>'Application assistance needed',
            'description'=>'Please check the status of my application on the portal.',
        ];
    }

    private function create(int $user=1, string $category='complaint'): object
    {
        return app(SupportCenter::class)->create(User::findOrFail($user),
            $this->data($category));
    }

    private function refundData(): array
    {
        return [
            'currency'=>'USD','amount'=>'49.50',
            'payment_reference'=>'charge_example_12345',
            'reason'=>'I paid for the service but it was not delivered as advertised.',
        ];
    }

    public function test_requester_creates_private_encrypted_ticket_and_sees_own_dashboard(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->post(route('support.store'), $this->data())
            ->assertRedirect();

        $this->assertDatabaseCount('support_tickets',1);
        $row = DB::table('support_tickets')->first();
        $this->assertStringStartsWith('JW-', $row->ticket_number);
        $this->assertSame('open',$row->status);
        $this->assertSame('normal',$row->priority);
        $this->assertStringNotContainsString('Please check', $row->description_encrypted);
        $this->assertStringContainsString('Please check',
            Crypt::decryptString($row->description_encrypted));
        $this->get(route('support.index'))->assertOk()->assertSee($row->ticket_number);
        $this->get(route('support.show',$row->id))->assertOk()->assertSee('Please check');
        $this->assertDatabaseHas('support_ticket_events',[
            'ticket_id'=>$row->id,'action'=>'created','new_status'=>'open',
        ]);
    }

    public function test_other_account_cannot_read_ticket_replies_or_request_refund(): void
    {
        $ticket = $this->create(1,'payment');
        app(SupportCenter::class)->reply(User::findOrFail(1), $ticket->id,
            'My private support history.',false);

        $this->actingAs(User::findOrFail(2))
            ->get(route('support.show',$ticket->id))->assertNotFound();
        $this->post(route('support.reply',$ticket->id),[
            'body'=>'Unauthorized change',
        ])->assertNotFound();
        $this->post(route('support.refund.request',$ticket->id),
            $this->refundData())->assertNotFound();
        $this->assertDatabaseCount('support_refund_requests',0);
    }

    public function test_admin_without_explicit_permission_cannot_open_support_queue(): void
    {
        $ticket=$this->create();
        $this->actingAs(User::findOrFail(4))
            ->get(route('support.show',$ticket->id))->assertNotFound();
        $this->get(route('support.index'))->assertOk()->assertDontSee($ticket->ticket_number);
        $this->post(route('support.reply',$ticket->id),
            ['body'=>'Please send personal data.'])->assertNotFound();
    }

    public function test_internal_notes_are_invisible_to_requester_and_stored_encrypted(): void
    {
        $ticket=$this->create();
        $this->actingAs(User::findOrFail(5))
            ->post(route('support.reply',$ticket->id),[
                'body'=>'Check evidence in case management system.',
                'internal_only'=>1,
            ])->assertRedirect();

        $entry=DB::table('support_ticket_replies')->first();
        $this->assertTrue((bool)$entry->internal_only);
        $this->assertStringNotContainsString('Check evidence',$entry->body_encrypted);

        $this->actingAs(User::findOrFail(1))
            ->get(route('support.show',$ticket->id))
            ->assertOk()->assertDontSee('Check evidence');
        $this->post(route('support.reply',$ticket->id),[
            'body'=>'Attempt internal access','internal_only'=>1,
        ])->assertForbidden();
        $this->assertDatabaseCount('support_ticket_replies',1);
    }

    public function test_customer_can_reopen_resolved_ticket_but_cannot_set_priority(): void
    {
        $ticket=$this->create();
        $center=app(SupportCenter::class);
        $resolved=$center->status(User::findOrFail(5),$ticket->id,'resolved','high');
        $this->assertSame('resolved',$resolved->status);
        $this->assertSame('high',$resolved->priority);

        $this->actingAs(User::findOrFail(1))
            ->post(route('support.status',$ticket->id),[
                'status'=>'open','priority'=>'urgent',
            ])->assertRedirect();
        $row=DB::table('support_tickets')->where('id',$ticket->id)->first();
        $this->assertSame('open',$row->status);
        $this->assertSame('high',$row->priority);
        $this->post(route('support.status',$ticket->id),[
            'status'=>'closed',
        ])->assertForbidden();
    }

    public function test_payment_refund_request_stores_minor_units_without_float_rounding(): void
    {
        $ticket=$this->create(1,'refund');
        $this->actingAs(User::findOrFail(1))
            ->post(route('support.refund.request',$ticket->id),
                $this->refundData())->assertRedirect();

        $this->assertDatabaseCount('support_refund_requests',1);
        $refund=DB::table('support_refund_requests')->first();
        $this->assertSame('requested',$refund->status);
        $this->assertSame(4950,(int)$refund->amount_minor);
        $this->assertStringNotContainsString('charge_example',
            $refund->payment_reference_encrypted);
        $this->assertDatabaseHas('support_refund_events',[
            'refund_id'=>$refund->id,'action'=>'requested',
        ]);
        $this->post(route('support.refund.request',$ticket->id),
            $this->refundData())->assertSessionHasErrors('amount');
    }

    public function test_refund_cannot_be_requested_on_nonpayment_ticket(): void
    {
        $ticket=$this->create(1,'complaint');
        $this->actingAs(User::findOrFail(1))
            ->post(route('support.refund.request',$ticket->id),
                $this->refundData())->assertStatus(422);
        $this->assertDatabaseCount('support_refund_requests',0);
    }

    public function test_refund_approval_and_external_settlement_require_two_officers(): void
    {
        $ticket=$this->create(1,'payment');
        $center=app(SupportCenter::class);
        $center->requestRefund(User::findOrFail(1),$ticket->id,$this->refundData());

        $reviewer=User::findOrFail(6);
        $settler=User::findOrFail(7);
        $center->decideRefund($reviewer,$ticket->id,'under_review');
        $approved=$center->decideRefund($reviewer,$ticket->id,'approved',
            'Receipt matched with payment provider.');

        $this->assertSame('approved',$approved->status);
        $this->assertNull($approved->settled_at);
        $this->assertDatabaseCount('support_refund_events',3);

        // Reviewer has review permission, not money settlement permission.
        $this->actingAs($reviewer)
            ->post(route('support.refund.review',$ticket->id),[
                'action'=>'settled',
                'settlement_reference'=>'stripe_refund_abc123',
            ])->assertForbidden();

        $this->actingAs($settler)
            ->post(route('support.refund.review',$ticket->id),[
                'action'=>'settled',
            ])->assertSessionHasErrors('settlement_reference');

        $settled=$center->decideRefund($settler,$ticket->id,'settled',
            'Verified manually against external payment console.',
            'stripe_refund_abc123');
        $this->assertSame('settled',$settled->status);
        $this->assertNotNull($settled->settled_at);
        $this->assertSame('stripe_refund_abc123',Crypt::decryptString(
            $settled->settlement_reference_encrypted));
        $this->assertDatabaseCount('support_refund_events',4);
    }

    public function test_refund_approving_officer_cannot_confirm_own_settlement(): void
    {
        $ticket=$this->create(1,'refund');
        $center=app(SupportCenter::class);
        $reviewer=User::findOrFail(6);
        $reviewer->givePermissionTo('confirm-refund-settlement');
        $center->requestRefund(User::findOrFail(1),$ticket->id,$this->refundData());
        $center->decideRefund($reviewer,$ticket->id,'under_review');
        $center->decideRefund($reviewer,$ticket->id,'approved','Verified receipt.');

        try {
            $center->decideRefund($reviewer,$ticket->id,'settled',
                null,'external_refund_confirmed_123');
            $this->fail('Same reviewer was allowed to settle refund.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('action',$e->errors());
        }
        $this->assertSame('approved',DB::table('support_refund_requests')
            ->where('ticket_id',$ticket->id)->value('status'));
    }

    public function test_unreviewed_refund_cannot_be_settled_and_rejected_cannot_be_approved(): void
    {
        $ticket=$this->create(1,'payment');
        $center=app(SupportCenter::class);
        $center->requestRefund(User::findOrFail(1),$ticket->id,$this->refundData());
        try {
            $center->decideRefund(User::findOrFail(7),$ticket->id,'settled',null,
                'external_123456');
            $this->fail('Unapproved refund was settled.');
        } catch(ValidationException $e) {
            $this->assertArrayHasKey('action',$e->errors());
        }
        $center->decideRefund(User::findOrFail(6),$ticket->id,'rejected',
            'Transaction could not be independently verified.');
        try {
            $center->decideRefund(User::findOrFail(6),$ticket->id,'approved',
                'Try again');
            $this->fail('Rejected refund changed to approved.');
        } catch(ValidationException $e) {
            $this->assertArrayHasKey('action',$e->errors());
        }
    }

    public function test_refund_claim_is_not_automatic_gateway_transaction(): void
    {
        $ticket=$this->create(1,'payment');
        $center=app(SupportCenter::class);
        $refund=$center->requestRefund(User::findOrFail(1),$ticket->id,$this->refundData());
        $this->assertSame('requested',$refund->status);
        $this->assertNull($refund->settled_at);
        $this->assertFalse((bool)config('support_center.automatic_refunds_enabled'));
    }

    public function test_support_api_scoped_to_customer_and_avoids_ciphertext(): void
    {
        $ticket=$this->create(1);
        Sanctum::actingAs(User::findOrFail(1),['*']);
        $this->getJson('/api/support-center/tickets/'.$ticket->id)
            ->assertOk()
            ->assertJsonPath('ticket.subject','Application assistance needed')
            ->assertJsonMissingPath('ticket.description_encrypted');

        Sanctum::actingAs(User::findOrFail(2),['*']);
        $this->getJson('/api/support-center/tickets/'.$ticket->id)
            ->assertNotFound();
    }

    public function test_feature_flag_off_and_guest_cannot_use_support_portal(): void
    {
        $this->get(route('support.index'))->assertRedirect();
        $this->actingAs(User::findOrFail(1));
        config()->set('support_center.enabled',false);
        $this->get(route('support.index'))->assertNotFound();
    }
    public function test_refund_review_staff_cannot_browse_unrelated_complaints(): void
    {
        $complaint=$this->create(1,'complaint');
        $payment=$this->create(1,'payment');
        $reviewer=User::findOrFail(6);

        $this->actingAs($reviewer)
            ->get(route('support.show',$complaint->id))->assertNotFound();
        $this->get(route('support.show',$payment->id))->assertNotFound();

        app(SupportCenter::class)->requestRefund(
            User::findOrFail(1),$payment->id,$this->refundData()
        );

        $this->get(route('support.show',$payment->id))->assertOk();
        $this->get(route('support.index'))
            ->assertOk()
            ->assertSee($payment->ticket_number)
            ->assertDontSee($complaint->ticket_number);
    }

    public function test_refund_reviewer_cannot_read_internal_support_agent_note(): void
    {
        $ticket=$this->create(1,'refund');
        app(SupportCenter::class)->requestRefund(
            User::findOrFail(1),$ticket->id,$this->refundData()
        );
        app(SupportCenter::class)->reply(
            User::findOrFail(5),$ticket->id,'Private staff notes',true
        );

        $this->actingAs(User::findOrFail(6))
            ->get(route('support.show',$ticket->id))
            ->assertOk()
            ->assertDontSee('Private staff notes');
    }

    public function test_support_agent_can_assign_approved_staff_with_audit(): void
    {
        $ticket=$this->create();
        $this->actingAs(User::findOrFail(5))
            ->post(route('support.assign',$ticket->id),[
                'assignee_id'=>5,
            ])->assertRedirect();

        $this->assertDatabaseHas('support_tickets',[
            'id'=>$ticket->id,'assigned_to'=>5,
        ]);
        $this->assertDatabaseHas('support_ticket_events',[
            'ticket_id'=>$ticket->id,'action'=>'assigned',
            'related_user_id'=>5,
        ]);

        $this->get(route('support.show',$ticket->id))
            ->assertOk()->assertSee('Update assignment');

        $this->post(route('support.assign',$ticket->id),[
            'assignee_id'=>'',
        ])->assertRedirect();
        $this->assertDatabaseHas('support_tickets',[
            'id'=>$ticket->id,'assigned_to'=>null,
        ]);
    }

    public function test_unprivileged_customer_and_refund_staff_cannot_assign_support_tickets(): void
    {
        $ticket=$this->create();
        $this->actingAs(User::findOrFail(1))
            ->post(route('support.assign',$ticket->id),[
                'assignee_id'=>5,
            ])->assertForbidden();

        $this->actingAs(User::findOrFail(5))
            ->post(route('support.assign',$ticket->id),[
                'assignee_id'=>2,
            ])->assertSessionHasErrors('assignee_id');
    }

}
