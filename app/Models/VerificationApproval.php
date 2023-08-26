<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationApproval extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'cpi_order_id',
        'stream_verification_id',
        'status'
    ];

    public function cpi_orders()
    {
        return $this->belongsTo(CpiOrder::class, 'cpi_order_id', 'id');
    }

    public function decline_reasons()
    {
        return $this->hasOne(DeclineReason::class, 'verification_approval_id', 'id');
    }

    public function stream_verifications()
    {
        return $this->belongsTo(StreamVerification::class, 'stream_verification_id', 'id');
    }
}
