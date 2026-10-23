<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
class SupportMessage extends Mailable {
 public function __construct(public string $ticketNumber,public string $ticketSubject,public string $messageBody,public string $ticketUrl,public string $senderAddress,public string $senderName){}
 public function build(){return $this->from($this->senderAddress,$this->senderName)->subject('['.$this->ticketNumber.'] '.$this->ticketSubject)->view('emails.support-message');}
}
