<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model {
    protected $guarded = [];
    public function user() { return $this->belongsTo(User::class); }
    public function ticket() { return $this->belongsTo(SupportTicket::class, 'ticket_id'); }
}
