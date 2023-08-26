<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuids;

class Stream extends Model
{
    use Uuids;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'modified_by'
    ];

    public function users()
    {
        return $this->hasManyThrough(User::class, UserHasStream::class, 'stream_id', 'id', 'id', 'user_id');
    }

    public function stream_section_head()
    {
        return $this->hasMany(StreamSectionHead::class, 'stream_id', 'id');
    }

    public function stream_verification()
    {
        return $this->hasMany(StreamVerification::class, 'stream_id', 'id');
    }

    public function cpi_orders()
    {
        return $this->hasMany(CpiOrder::class, 'stream_id', 'id');
    }
}
