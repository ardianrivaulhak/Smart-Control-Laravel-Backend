<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verification extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'stream_id',
        'verification_1',
        'verification_2',
    ];

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }
}
