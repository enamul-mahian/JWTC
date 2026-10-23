<?php
namespace App\Services;
use App\Models\User;
use App\Mail\SupportMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
class SupportNotifications {
 public function notify(User $actor,object $ticket,string $body,string $key): ?string {
  if(!Schema::hasTable('support_mail_deliveries'))return null;
  if((int)$actor->id!==(int)$ticket->requester_id)$email=User::find($ticket->requester_id)?->email;
  elseif(!empty($ticket->recipient_company_id))$email=DB::table('companies')->join('users','users.id','=','companies.user_id')->where('companies.id',$ticket->recipient_company_id)->whereNull('users.deleted_at')->value('users.email');
  else $email=config('support_center.notification_address');
  $existing=DB::table('support_mail_deliveries')->where('event_key',$key)->first();if($existing)return $existing->status;
  $id=DB::table('support_mail_deliveries')->insertGetId(['ticket_id'=>$ticket->id,'event_key'=>$key,'recipient_encrypted'=>encrypt((string)$email),'message_encrypted'=>encrypt($body),'status'=>'pending','attempts'=>0,'created_at'=>now(),'updated_at'=>now()]);
  return $this->deliver($id);
 }
 public function deliver(int $id): string {
  $record=DB::transaction(function()use($id){$r=DB::table('support_mail_deliveries')->where('id',$id)->lockForUpdate()->first();abort_unless($r,404);if(in_array($r->status,['accepted','sending'],true)){$r->skip_delivery=true;return $r;}DB::table('support_mail_deliveries')->where('id',$id)->update(['status'=>'sending','attempts'=>$r->attempts+1,'updated_at'=>now()]);return $r;});
  if(isset($record->skip_delivery))return $record->status;
  try {
   $email=decrypt($record->recipient_encrypted);if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new \RuntimeException('Recipient not configured');
   $profiles=app(StudioSmtpProfiles::class);$smtp=$profiles->settings($profiles->defaultId());
   if(!filter_var($smtp['from_address'],FILTER_VALIDATE_EMAIL))throw new \RuntimeException('Sender not configured');
   $ticket=DB::table('support_tickets')->where('id',$record->ticket_id)->first();
   Mail::mailer($smtp['mailer'])->to($email)->send(new SupportMessage($ticket->ticket_number,$ticket->subject,decrypt($record->message_encrypted),route('support.show',$ticket->id),$smtp['from_address'],$smtp['from_name']));
   DB::table('support_mail_deliveries')->where('id',$id)->update(['status'=>'accepted','accepted_at'=>now(),'updated_at'=>now()]);return 'accepted';
  }catch(\Throwable $e){DB::table('support_mail_deliveries')->where('id',$id)->update(['status'=>'failed','updated_at'=>now()]);return 'failed';}
 }
}
