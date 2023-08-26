<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogTrail extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'timestamp',
        'rev',
        'changed_by'
    ];

    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function log_trail_details()
    {
        return $this->hasMany(LogTrailDetail::class, 'log_trail_id', 'id');
    }
}
