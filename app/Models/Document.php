<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory;
    use Uuids;
    use SoftDeletes;

    public function form_control_process()
    {
        return $this->hasMany(FormControlProcess::class, 'document_id', 'id');
    }

    public function log_trails()
    {
        return $this->hasMany(LogTrail::class, 'document_id', 'id');
    }

    public function cpi_orders()
    {
        return $this->hasMany(CpiOrder::class, 'document_id', 'id');
    }
}
