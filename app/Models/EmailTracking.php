<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTracking extends Model
{
    protected $table = 'email_tracking';

    protected $fillable = [
        'document_id',
        'company_id',
        'recipient_email',
        'tracking_token',
        'sent_at',
        'opened_at',
        'opens_count',
        'last_opened_at',
        'clicked_at',
        'clicks_count',
        'last_clicked_at',
        'client_ip',
        'user_agent',
        'alert_sent_at',
    ];

    protected $casts = [
        'sent_at'          => 'datetime',
        'opened_at'        => 'datetime',
        'last_opened_at'   => 'datetime',
        'clicked_at'       => 'datetime',
        'last_clicked_at'  => 'datetime',
        'alert_sent_at'    => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
