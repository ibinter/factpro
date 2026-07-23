<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {
    protected $guarded = [];
    protected $casts = ['resolved_at' => 'datetime'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($ticket) {
            $count = static::withTrashed()->count() + 1;
            $ticket->ticket_number = 'TKT-'.now()->year.'-'.str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function replies() { return $this->hasMany(SupportTicketReply::class, 'ticket_id'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
}
