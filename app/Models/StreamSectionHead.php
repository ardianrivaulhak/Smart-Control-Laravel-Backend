<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuids;

class StreamSectionHead extends Model
{
    use SoftDeletes;
    use Uuids;
    use HasFactory;
    protected $table = 'stream_section_head';
    protected $fillable = [
        'stream_id',
        'section_id',
        'user_id',
        'modified_by'
    ];

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function section_approvals()
    {
        return $this->hasMany(SectionApproval::class, 'stream_section_head_id', 'id');
    }
}
