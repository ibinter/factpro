<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'to_phone',
        'message_type',
        'template_name',
        'body',
        'status',
        'wa_message_id',
        'sent_at',
        'error_message',
        'document_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
