<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTransaction extends Model
{
    protected $fillable = [
        'certificate_id',
        'user_id',
        'action',
        'action_details',
        'ip_address',
        'user_agent'
    ];

    // Relationships
    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
