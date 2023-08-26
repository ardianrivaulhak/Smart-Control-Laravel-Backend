<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeclineReason extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'section_approval_id',
        'verification_approval_id',
        'reason'
    ];

    public function section_approvals()
    {
        return $this->belongsTo(SectionApproval::class, 'section_approval_id', 'id');
    }

    public function verification_approvals()
    {
        return $this->belongsTo(VerificationApproval::class, 'verification_approval_id', 'id');
    }
}
