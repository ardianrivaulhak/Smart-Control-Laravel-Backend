<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogTrailDetail extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'log_trail_id',
        'change'
    ];

    public function log_trails()
    {
        return $this->belongsTo(LogTrail::class, 'log_trail_id', 'id');
    }
}
