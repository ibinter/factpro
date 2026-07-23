<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerVisit extends Model
{
    protected $fillable = [
        'company_id','user_id','customer_id','customer_name','visit_type','status',
        'lat_start','lng_start','lat_end','lng_end','address_visited',
        'planned_at','started_at','ended_at','duration_minutes',
        'objective','report','outcome','document_id','meta',
    ];

    protected $casts = [
        'planned_at'  => 'datetime',
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
        'meta'        => 'array',
    ];

    public function company()  { return $this->belongsTo(Company::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function document() { return $this->belongsTo(Document::class); }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_minutes) return '—';
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $h > 0 ? "{$h}h" . sprintf('%02d', $m) : "{$m} min";
    }
}
