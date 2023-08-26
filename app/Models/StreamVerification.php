<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuids;

class StreamVerification extends Model
{
    use SoftDeletes;
    use Uuids;
    use HasFactory;
    protected $table = 'stream_verifications';

    protected $fillable = [
        'id',
        'stream_id',
        'type',
        'name',
        'user_id',
        'modified_by'
    ];

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function verification_approvals()
    {
        return $this->hasMany(VerificationApproval::class, 'stream_verification_id', 'id');
    }
}
